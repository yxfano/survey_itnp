document.addEventListener('DOMContentLoaded', function() {
    const layananSelect = document.getElementById('layanan');
    const surveyQuestions = document.getElementById('survey-questions');
    const feedbackTextarea = document.getElementById('feedback');
    const surveyForm = document.getElementById('survey-form');

    // Menambahkan kode untuk mengisi tanggal otomatis
    const today = new Date().toISOString().split('T')[0];
    const surveyDateInput = document.getElementById('surveyDate');
    if (surveyDateInput) {
        surveyDateInput.value = today;
    }

    layananSelect.addEventListener('change', function() {
        const selectedLayanan = this.value;
        if (selectedLayanan) {
            fetch(`get_questions.php?layanan=${encodeURIComponent(selectedLayanan)}`)
                .then(response => response.json())
                .then(questions => {
                    surveyQuestions.innerHTML = '';
                    questions.forEach((question) => {
                        const questionDiv = document.createElement('div');
                        questionDiv.className = 'form-group';
                        questionDiv.innerHTML = `
                            <label for="q${question.id}">${question.text}</label>
                            <div id="q${question.id}">
                                ${[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(value => 
                                    `<label style="display: inline-block; margin-right: 10px;">
                                        <input type="radio" name="q${question.id}" value="${value}" required> ${value}
                                    </label>`
                                ).join('')}
                            </div>
                        `;
                        surveyQuestions.appendChild(questionDiv);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    surveyQuestions.innerHTML = '<p class="error">Terjadi kesalahan saat memuat pertanyaan.</p>';
                });
        } else {
            surveyQuestions.innerHTML = '';
        }
    });

    surveyForm.addEventListener('submit', function(event) {
        const ratings = Array.from(surveyQuestions.querySelectorAll('input[type="radio"]:checked')).map(input => parseInt(input.value));
        const averageRating = ratings.reduce((sum, rating) => sum + rating, 0) / ratings.length;

        if (averageRating < 5 && !feedbackTextarea.value.trim()) {
            event.preventDefault();
            alert('Harap memberikan masukan terhadap ketidakpuasan anda.');
            feedbackTextarea.required = true;
        } else {
            feedbackTextarea.required = false;
        }
    });
});
