#include <wiringPi.h>
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <fcntl.h>
#include <unistd.h>
#include <string.h>
#include <signal.h>
#include <semaphore.h>
#include <pthread.h>

#define PIN_STEP_X 0  // GPIO 17
#define PIN_DIR_X 1   // GPIO 18
#define PIN_STEP_Y 3  // GPIO 17
#define PIN_DIR_Y 4   // GPIO 18
#define PIPE_FILE "/fifo/teste/fifo"

#define MOTOR_STEP 1.8/8
#define Ix 19.2*120./17
#define Iy 19.2*206./18
#define DAY ((23.*60.+56.)*60.+4.)
#define ROT 360.

// Variáveis globais
int fd;                   // FILE descriptor do FIFO
char CMD[10000] = "";     // Comando
char command[2] = "";
int com = 0;
unsigned int motor, dir;
unsigned int deg_singnal, deg, deg_min, deg_sec, hour, min;
float timeX, timeY;
unsigned long long int duration = 0;
int process;
sem_t S1;

// Struct para argumentos das threads
typedef struct {
    int dlyTime;
    int stepPin;
} MotorArgs;

// Funções de inicialização
void setupTMC2208() {
    pinMode(PIN_STEP_X, OUTPUT);
    pinMode(PIN_DIR_X, OUTPUT);
    pinMode(PIN_STEP_Y, OUTPUT);
    pinMode(PIN_DIR_Y, OUTPUT);
}

void initFIFO() {
    fd = open(PIPE_FILE, O_RDONLY);
    if (fd == -1) {
        perror("Erro ao abrir o Named Pipe");
        exit(EXIT_FAILURE);
    }
}

void saveProcessID() {
    process = getpid();
    FILE *pidFile = fopen("/fifo/teste/PID.txt", "w");
    if (pidFile == NULL) {
        perror("Não foi possível criar o arquivo PID.txt");
        exit(EXIT_FAILURE);
    }
    fprintf(pidFile, "%d", process);
    fclose(pidFile);
}

// Manipulador de sinal
void handler(int signum) {
    ssize_t bytesRead;
    memset(CMD, 0, sizeof(CMD));

    while ((bytesRead = read(fd, CMD, sizeof(CMD) - 1)) > 0) {
        CMD[bytesRead] = '\0';
        printf("Dados recebidos: %s\n", CMD);
    }

    if (bytesRead < 0) {
        perror("Erro ao ler o FIFO");
    }

    sscanf(CMD, "%2s", command);
    com = strtol(command, NULL, 16);
    if (com & 0x02) {
        sscanf(CMD + 2, "%1x%1x", &motor, &dir);
    } else if (com & 0x04) {
        sscanf(CMD + 2, "%1x%2d%2d%2d%2d%2d", &deg_singnal, &deg, &deg_min, &deg_sec, &hour, &min);
        duration = (hour * 60 + min) * 60 * 10000;
        printf("%llu\n", duration);
    }

    sem_post(&S1);
}

// Função para controle dos motores
void* direction(void* arg) {
    MotorArgs* motorArgs = (MotorArgs*) arg;
    int dlyTime = motorArgs->dlyTime;
    int stepPin = motorArgs->stepPin;

    while (1) {
        if (com & 0x01) {
            break;
        }
        digitalWrite(stepPin, HIGH);
        delayMicroseconds(dlyTime / 2.0);
        digitalWrite(stepPin, LOW);
        delayMicroseconds(dlyTime / 2.0);
    }
    return NULL;
}

// Funções auxiliares
void calcDlyy() {
    float lat = deg + (deg_min + deg_sec / 60.0) / 60.0;
    float rad = lat * (M_PI / 180.0);
    timeX = 1000000 * (DAY * MOTOR_STEP / (ROT * Ix)) / cos(rad);
    timeY = 1000000 * (DAY * MOTOR_STEP / (ROT * Iy)) / sin(rad);
}

void waitOneMinute() {
    struct timespec req = { .tv_sec = 60, .tv_nsec = 0 };
    nanosleep(&req, NULL);
}

void controlMotors() {
    //thread1 corresponde ao acionamento do motorX e thread2 para o motor y
    pthread_t thread1, thread2;

    if (com & 0x02) {
        if (motor & 0x01) {
            digitalWrite(PIN_DIR_X, dir & 0x01 ? HIGH : LOW);
            MotorArgs motorArgs1 = {200, PIN_STEP_X};
            pthread_create(&thread1, NULL, direction, &motorArgs1);
            pthread_join(thread1, NULL);
        } else if (motor & 0x02) {
            digitalWrite(PIN_DIR_Y, dir & 0x01 ? HIGH : LOW);
            MotorArgs motorArgs2 = {200, PIN_STEP_Y};
            pthread_create(&thread2, NULL, direction, &motorArgs2);
            pthread_join(thread2, NULL);
        } else if (motor & 0x04) {
            digitalWrite(PIN_DIR_X, dir & 0x01 ? HIGH : LOW);
            digitalWrite(PIN_DIR_Y, dir & 0x04 ? HIGH : LOW);
            MotorArgs motorArgs1 = {200, PIN_STEP_X};
            MotorArgs motorArgs2 = {200, PIN_STEP_Y};
            pthread_create(&thread1, NULL, direction, &motorArgs1);
            pthread_create(&thread2, NULL, direction, &motorArgs2);
            pthread_join(thread1, NULL);
            pthread_join(thread2, NULL);
        }
    } else if (com & 0x04) {
        calcDlyy();
        digitalWrite(PIN_DIR_X, LOW);
        digitalWrite(PIN_DIR_Y, deg_singnal & 0x01 ? LOW : HIGH);
        MotorArgs motorArgs1 = {timeX, PIN_STEP_X};
        MotorArgs motorArgs2 = {timeY, PIN_STEP_Y};
        pthread_create(&thread1, NULL, direction, &motorArgs1);
        pthread_create(&thread2, NULL, direction, &motorArgs2);

        unsigned long startTime = micros();
        while (1) {
            unsigned long elapsedTime = micros() - startTime;
            if (elapsedTime >= duration * 100) {
                pthread_cancel(thread1);
                pthread_cancel(thread2);
                break;
            }
            waitOneMinute();
        }

        pthread_join(thread1, NULL);
        pthread_join(thread2, NULL);
    }
}

// Função principal
int main() {
    sem_init(&S1, 0, 0);

    if (wiringPiSetup() == -1) {
        printf("Erro ao inicializar o wiringPi.\n");
        return 1;
    }

    setupTMC2208();
    saveProcessID();
    initFIFO();

    if (signal(SIGHUP, handler) == SIG_ERR) {
        perror("Não foi possível registrar o manipulador de sinal");
        exit(EXIT_FAILURE);
    }

    while (1) {
        sem_wait(&S1);
        controlMotors();
    }

    return 0;
}
