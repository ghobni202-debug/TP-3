$(document).ready(function() {
    loadCurrentGrades();
    
    function loadCurrentGrades() {
        $.get('api/gpa.php', { action: 'current' }, function(data) {
            if (data.error) {
                $('#currentGrades').html('<div class="alert alert-warning">' + data.error + '</div>');
                return;
            }
            
            var gpaClass = getGpaClass(data.gpa);
            var html = '<h3>' + data.semester.label + ' - ' + data.semester.academic_year + '</h3>';
            html += '<div class="alert ' + gpaClass + '">Semester GPA: <strong>' + (data.gpa !== null ? data.gpa : 'Pending') + '</strong></div>';
            html += '<table class="table table-bordered">';
            html += '<thead><tr><th>Course</th><th>Credits</th><th>Grade</th><th>Grade Points</th></tr></thead><tbody>';
            
            $.each(data.courses, function(i, course) {
                var gradeDisplay = course.grade !== null ? course.grade : '<span class="text-muted">Pending</span>';
                var pointsDisplay = course.grade_points !== null ? course.grade_points : '-';
                html += '<tr>' +
                    '<td>' + course.name + '</td>' +
                    '<td>' + course.credits + '</td>' +
                    '<td>' + gradeDisplay + '</td>' +
                    '<td>' + pointsDisplay + '</td>' +
                    '</tr>';
            });
            
            html += '</tbody></table>';
            $('#currentGrades').html(html);
        }, 'json');
    }
    
    function getGpaClass(gpa) {
        if (gpa === null) return 'alert-secondary';
        if (gpa >= 3.7) return 'alert-success';
        if (gpa >= 3.0) return 'alert-info';
        if (gpa >= 2.0) return 'alert-warning';
        return 'alert-danger';
    }
});
