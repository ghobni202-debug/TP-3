<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GPA History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Student: <?= htmlspecialchars($_SESSION['name']) ?> - GPA History</span>
            <div>
                <a href="index.php?page=student.dashboard" class="btn btn-outline-info me-2">Current</a>
                <a href="index.php?page=logout" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>GPA History</h2>
        <canvas id="gpaChart" width="400" height="200"></canvas>
        <div id="historyData" class="mt-4"></div>
    </div>
    
    <script>
        $(document).ready(function() {
            $.get('api/gpa.php', { action: 'history' }, function(data) {
                var labels = [];
                var gpas = [];
                var html = '';
                
                $.each(data, function(i, semester) {
                    labels.push(semester.label + ' ' + semester.academic_year);
                    gpas.push(semester.gpa !== null ? semester.gpa : null);
                    
                    html += '<div class="card mb-3">';
                    html += '<div class="card-header"><strong>' + semester.label + ' - ' + semester.academic_year + '</strong>';
                    html += '<span class="float-end">GPA: ' + (semester.gpa !== null ? semester.gpa : 'N/A') + '</span></div>';
                    html += '<div class="card-body">';
                    html += '<table class="table table-sm">';
                    html += '<thead><tr><th>Course</th><th>Credits</th><th>Grade</th><th>Points</th></tr></thead><tbody>';
                    
                    $.each(semester.courses, function(j, course) {
                        html += '<tr>' +
                            '<td>' + course.name + '</td>' +
                            '<td>' + course.credits + '</td>' +
                            '<td>' + (course.grade !== null ? course.grade : 'Pending') + '</td>' +
                            '<td>' + (course.grade_points !== null ? course.grade_points : '-') + '</td>' +
                            '</tr>';
                    });
                    
                    html += '</tbody></table></div></div>';
                });
                
                $('#historyData').html(html);
                
                // رسم Chart.js
                new Chart(document.getElementById('gpaChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'GPA',
                            data: gpas,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                min: 0,
                                max: 4,
                                title: { display: true, text: 'GPA' }
                            }
                        }
                    }
                });
            }, 'json');
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
