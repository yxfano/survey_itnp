document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk membuat grafik
    window.createChart = function(data) {
        const ctx = document.getElementById('avgRatingsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.layanan),
                datasets: [{
                    label: 'Rata-rata Nilai',
                    data: data.map(item => item.avg_rating),
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10
                    }
                },
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Rata-rata Nilai Survey per Layanan'
                    }
                }
            }
        });
    };

    // Fungsi untuk mengedit pertanyaan
    window.editQuestion = function(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const layanan = row.querySelector('td:nth-child(2)').textContent;
        const text = row.querySelector('td:nth-child(3)').textContent;

        row.innerHTML = `
            <td>${id}</td>
            <td>
                <select name="layanan" required>
                    <option value="Printer" ${layanan === 'Printer' ? 'selected' : ''}>Printer</option>
                    <option value="Laptop" ${layanan === 'Laptop' ? 'selected' : ''}>Laptop</option>
                    <option value="Penyedia Layanan IT" ${layanan === 'Penyedia Layanan IT' ? 'selected' : ''}>Penyedia Layanan IT</option>
                </select>
            </td>
            <td><textarea name="text" required>${text}</textarea></td>
            <td>
                <button onclick="saveQuestion(${id})">Simpan</button>
                <button onclick="cancelEdit(${id})">Batal</button>
            </td>
        `;
    };

    // Fungsi untuk menyimpan pertanyaan yang diedit
    window.saveQuestion = function(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const layanan = row.querySelector('select[name="layanan"]').value;
        const text = row.querySelector('textarea[name="text"]').value;

        fetch('manage_questions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=edit&id=${id}&layanan=${encodeURIComponent(layanan)}&text=${encodeURIComponent(text)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.innerHTML = `
                    <td>${id}</td>
                    <td>${layanan}</td>
                    <td>${text}</td>
                    <td>
                        <button onclick="editQuestion(${id})">Edit</button>
                        <button onclick="deleteQuestion(${id})">Hapus</button>
                    </td>
                `;
            } else {
                alert('Gagal menyimpan perubahan.');
            }
        })
        .catch(error => console.error('Error:', error));
    };

    // Fungsi untuk membatalkan edit
    window.cancelEdit = function(id) {
        location.reload();
    };

    // Fungsi untuk menghapus pertanyaan
    window.deleteQuestion = function(id) {
        if (confirm('Anda yakin ingin menghapus pertanyaan ini?')) {
            fetch('manage_questions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                } else {
                    alert('Gagal menghapus pertanyaan.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    };
});