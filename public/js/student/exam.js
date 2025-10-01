class ExamManager {
    constructor(config) {
        this.config = config;
        this.timerInterval = null;
        this.init();
    }

    init() {
        this.initTimer();
        this.initEventListeners();
    }

    initTimer() {
        const endTime = new Date(this.config.endTime).getTime();
        if (!localStorage.getItem(`examEndTime_${this.config.examId}`)) {
            localStorage.setItem(`examEndTime_${this.config.examId}`, endTime);
        }

        const savedEndTime = parseInt(
            localStorage.getItem(`examEndTime_${this.config.examId}`)
        );
        this.startTimer(savedEndTime);
    }

    startTimer(savedEndTime) {
        this.updateTimer(savedEndTime);

        this.timerInterval = setInterval(() => {
            this.updateTimer(savedEndTime);
        }, 1000);
    }

    updateTimer(savedEndTime) {
        const now = new Date().getTime();
        const distance = savedEndTime - now;

        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("timer").innerHTML =
            ("0" + hours).slice(-2) +
            ":" +
            ("0" + minutes).slice(-2) +
            ":" +
            ("0" + seconds).slice(-2);

        if (distance < 0) {
            this.handleTimeUp();
        }
    }

    handleTimeUp() {
        clearInterval(this.timerInterval);
        document.getElementById("timer").innerHTML = "WAKTU HABIS";
        localStorage.removeItem(`examEndTime_${this.config.examId}`);

        this.showAlert("Waktu ujian telah habis!");
        document.getElementById("autoFinishForm").submit();
    }

    initEventListeners() {
        // Checkbox ragu-ragu
        document
            .getElementById("markDoubtCheckbox")
            .addEventListener("change", () => this.handleDoubtChange());

        // Navigasi soal
        document.querySelectorAll(".question-nav").forEach((button) => {
            button.addEventListener("click", (e) =>
                this.handleQuestionNavigation(e)
            );
        });

        // Tombol navigasi
        if (document.getElementById("prevQuestion")) {
            document
                .getElementById("prevQuestion")
                .addEventListener("click", () => this.navigateToPrevQuestion());
        }

        if (document.getElementById("nextQuestion")) {
            document
                .getElementById("nextQuestion")
                .addEventListener("click", () => this.navigateToNextQuestion());
        }

        if (document.getElementById("finishExam")) {
            document
                .getElementById("finishExam")
                .addEventListener("click", () => this.handleFinishExam());
        }

        // Form selesai ujian
        document
            .getElementById("finishForm")
            .addEventListener("submit", (e) => this.handleFinishForm(e));

        // Radio buttons
        document.querySelectorAll('input[name="answer"]').forEach((input) => {
            input.addEventListener("change", () => this.saveAnswer());
        });

        // Prevent form submission on enter
        document
            .getElementById("answerForm")
            .addEventListener("keydown", (e) => {
                if (e.key === "Enter") e.preventDefault();
            });
    }

    async handleDoubtChange() {
        const checkbox = document.getElementById("markDoubtCheckbox");
        const originalState = checkbox.checked;
        checkbox.disabled = true;

        const success = await this.saveAnswer();
        checkbox.disabled = false;

        if (!success) {
            checkbox.checked = !originalState;
        } else {
            window.location.reload();
        }
    }

    async handleQuestionNavigation(e) {
        e.preventDefault();
        const button = e.currentTarget;
        const kodeSoal = button.getAttribute("data-kode-soal");
        const isCurrent = button.getAttribute("data-is-current") === "true";

        if (isCurrent) return;

        const success = await this.saveAnswer();
        if (success) {
            window.location.href = `${this.config.examDoUrl}/${kodeSoal}`;
        }
    }

    async navigateToPrevQuestion() {
        const success = await this.saveAnswer();
        if (success && this.config.prevQuestionKode) {
            window.location.href = `${this.config.examDoUrl}/${this.config.prevQuestionKode}`;
        }
    }

    async navigateToNextQuestion() {
        const success = await this.saveAnswer();
        if (success && this.config.nextQuestionKode) {
            window.location.href = `${this.config.examDoUrl}/${this.config.nextQuestionKode}`;
        }
    }

    async handleFinishExam() {
        const success = await this.saveAnswer();
        if (success) {
            this.showConfirm(
                "Apakah Anda yakin ingin menyelesaikan ujian?",
                (confirmed) => {
                    if (confirmed) {
                        document.getElementById("finishForm").submit();
                    }
                }
            );
        }
    }

    handleFinishForm(e) {
        e.preventDefault();
        this.showConfirm(
            "Apakah Anda yakin ingin menyelesaikan ujian?",
            (confirmed) => {
                if (confirmed) {
                    e.target.submit();
                }
            }
        );
    }

    async saveAnswer() {
        const selectedAnswer = document.querySelector(
            'input[name="answer"]:checked'
        );
        const answerValue = selectedAnswer ? selectedAnswer.value : null;
        const markDoubtValue = document.getElementById("markDoubtCheckbox")
            .checked
            ? 1
            : 0;

        try {
            const response = await fetch(this.config.saveAnswerUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.config.csrfToken,
                },
                body: JSON.stringify({
                    answer: answerValue,
                    mark_doubt: markDoubtValue,
                }),
            });

            const data = await response.json();

            if (data.success) {
                return true;
            } else {
                this.showAlert(
                    "Gagal menyimpan jawaban: " +
                        (data.message || "Terjadi kesalahan")
                );
                return false;
            }
        } catch (error) {
            console.error("Error:", error);
            this.showAlert("Terjadi kesalahan saat menyimpan jawaban");
            return false;
        }
    }

    showAlert(message) {
        document.getElementById("alertModalBody").textContent = message;
        const alertModal = new bootstrap.Modal(
            document.getElementById("alertModal")
        );
        alertModal.show();
    }

    showConfirm(message, callback) {
        document.getElementById("confirmModalBody").textContent = message;
        const confirmModal = new bootstrap.Modal(
            document.getElementById("confirmModal")
        );

        const actionBtn = document.getElementById("confirmModalAction");
        const newActionBtn = actionBtn.cloneNode(true);
        actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);

        newActionBtn.onclick = () => {
            confirmModal.hide();
            callback(true);
        };

        confirmModal.show();
    }
}

function initExam(config) {
    return new ExamManager(config);
}
