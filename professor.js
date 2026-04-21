$(document).ready(function() {
    // تحميل المواد عند اختيار الفصل
    $('#semesterSelect').change(function() {
        var semId = $(this).val();
        if (!semId) return;
        
        $.get('api/grades.php', {
            action: 'courses',
            semester_id: semId
        }, function(data) {
            var opts = '<option value="">-- Select course --</option>';
            $.each(data, function(i, course) {
                opts += '<option value="' + course.id + '">' + course.name + '</option>';
            });
            $('#courseSelect').html(opts).prop('disabled', false);
            $('#gradeTable').hide();
        }, 'json');
    });
    
    // تحميل الطلاب عند اختيار المادة
    $('#courseSelect').change(function() {
        var semId = $('#semesterSelect').val();
        var courseId = $(this).val();
        if (!semId || !courseId) return;
        
        $.get('api/grades.php', {
            action: 'students',
            semester_id: semId,
            course_id: courseId
        }, function(students) {
            var html = '<thead><tr><th>Student Name</th><th>Student ID</th><th>Grade</th></tr></thead><tbody>';
            $.each(students, function(i, s) {
                var gradeVal = s.grade !== null ? s.grade : '';
                html += '<tr>' +
                    '<td>' + s.name + '</td>' +
                    '<td>' + s.id + '</td>' +
                    '<td>' +
                    '<select class="form-select grade-input" data-student="' + s.id + '">' +
                    buildGradeOptions(gradeVal) +
                    '</select>' +
                    '</td>' +
                    '</tr>';
            });
            html += '</tbody>';
            $('#gradeTable').html(html).show();
        }, 'json');
    });
    
    // حفظ الدرجات
    $('#saveGradesBtn').click(function() {
        var semId = $('#semesterSelect').val();
        var courseId = $('#courseSelect').val();
        var grades = [];
        
        $('.grade-input').each(function() {
            grades.push({
                student_id: $(this).data('student'),
                grade: $(this).val()
            });
        });
        
        $.post('api/grades.php', {
            action: 'save',
            semester_id: semId,
            course_id: courseId,
            grades: grades
        }, function(res) {
            var cls = res.success ? 'success' : 'danger';
            var msg = res.success ? res.saved + ' grade(s) saved successfully.' : res.error;
            $('#feedback').html('<div class="alert alert-' + cls + '">' + msg + '</div>');
            setTimeout(function() {
                $('#feedback').empty();
            }, 3000);
        }, 'json');
    });
    
    function buildGradeOptions(selected) {
        var grades = [
            ['', '-- Select Grade --'],
            ['4.0', 'A'],
            ['3.0', 'B'],
            ['2.0', 'C'],
            ['1.0', 'D'],
            ['0.0', 'F']
        ];
        return grades.map(function(g) {
            return '<option value="' + g[0] + '"' + (g[0] == selected ? ' selected' : '') + '>' + g[1] + '</option>';
        }).join('');
    }
});
