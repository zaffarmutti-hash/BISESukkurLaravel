<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment to Exam Gap Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Enrollment to Exam Gap Report</h1>
    <div class="meta">
        {{ $school->name ?? 'School' }}
        @if($activeYear) — {{ $activeYear->label }} @endif
        — Generated {{ $generated_at->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Father</th>
                <th>Enrollment No</th>
                <th>Class</th>
                <th>Group</th>
                <th>Days Since Enrollment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['full_name'] }}</td>
                    <td>{{ $student['father_name'] }}</td>
                    <td>{{ $student['enrollment_number'] }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($student['class_level'])) }}</td>
                    <td>{{ ucfirst($student['subject_group']) }}</td>
                    <td>{{ $student['days_since_enrollment'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
