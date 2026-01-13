<?php

return [
    [
        'title' => null,
        'key' => 'dashboard',
        'roles' => ['admin', 'lecturer', 'student', 'koordinator'],
        'context' => ['pssk', 'pspd'],
        'items' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'pattern' => 'dashboard',
                'icon' => 'fa-chart-line',
            ],
        ]
    ],

    [
        'title' => 'User Management',
        'roles' => ['admin'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Students',
                'route' => 'admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-user-graduate'
            ],
            [
                'label' => 'Lecturers',
                'route' => 'admin.users.index',
                'params' => ['type' => 'lecturer'],
                'pattern' => 'admin/users/lecturer*',
                'icon' => 'fa-chalkboard-teacher'
            ],
            [
                'label' => 'Admin',
                'route' => 'admin.users.index',
                'params' => ['type' => 'admin'],
                'pattern' => 'admin/users/admin*',
                'icon' => 'fa-user-gear'
            ],
        ]
    ],

    [
        'title' => 'Assessment',
        'roles' => ['lecturer'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Schedule',
                'route' => 'attendance.index',
                'pattern' => 'attendance*',
                'icon' => 'fa-clipboard-check'
            ],
            [
                'label' => 'Nilai Tutor',
                'route' => 'tutors',
                'pattern' => 'tutor*',
                'icon' => 'fa-star'
            ],
        ]
    ],

    [
        'title' => 'Course',
        'roles' => ['admin', 'koordinator'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Manage Courses',
                'route' => 'courses.index',
                'pattern' => 'course*',
                'icon' => 'fa-book'
            ],
            [
                'label' => 'Academic Year',
                'route' => 'admin.semester.index',
                'pattern' => 'semester*',
                'icon' => 'fa-calendar',
                'roles' => ['admin']
            ],
        ]
    ],

    [
        'title' => 'Exams',
        'roles' => ['koordinator'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Upcoming Exam',
                'route' => 'exams.index',
                'params' => ['status' => 'upcoming'],
                'pattern' => 'exams/upcoming*',
                'icon' => 'fa-calendar-alt'
            ],
            [
                'label' => 'Ongoing Exams',
                'route' => 'exams.index',
                'params' => ['status' => 'ongoing'],
                'pattern' => 'exams/ongoing*',
                'icon' => 'fa-clipboard-list'
            ],
            [
                'label' => 'Previous Exam',
                'route' => 'exams.index',
                'params' => ['status' => 'previous'],
                'pattern' => 'exams/previous*',
                'icon' => 'fa-history'
            ],
        ]
    ],

    [
        'title' => 'Exams Report',
        'roles' => ['koordinator'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Ungraded',
                'route' => 'lecturer.results.index',
                'params' => ['status' => 'ungraded'],
                'pattern' => 'ungraded*',
                'icon' => 'fa-clipboard-list'
            ],
            [
                'label' => 'Published',
                'route' => 'lecturer.results.index',
                'params' => ['status' => 'published'],
                'pattern' => 'published*',
                'icon' => 'fa-clipboard-list'
            ],
        ]
    ],

    [
        'title' => 'Student Exams',
        'roles' => ['student'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Upcoming Exam',
                'route' => 'student.studentExams.index',
                'params' => ['status' => 'upcoming'],
                'pattern' => 'student/exams/upcoming',
                'icon' => 'fa-file'
            ],
            [
                'label' => 'Previous Exam',
                'route' => 'student.studentExams.index',
                'params' => ['status' => 'previous'],
                'pattern' => 'student/exams/previous*',
                'icon' => 'fa-history'
            ],
        ]
    ],

    [
        'title' => 'Attendance',
        'roles' => ['student'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Previous Attendance',
                'route' => 'student.attendance.index',
                'pattern' => 'student/attendance*',
                'icon' => 'fa-calendar-alt'
            ],
        ]
    ],
    //PSPD
    //admin/lecturer pspd
    [
        'title' => 'Student Management',
        'roles' => ['admin'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Students',
                'route' => 'admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-user-graduate'
            ],
        ]
    ],
    [
        'title' => 'Logbook Mahasiswa',
        'roles' => ['lecturer'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Waiting for Approval',
                'route' => 'attendance.index',
                'pattern' => 'attendance*',
                'icon' => 'fa-clipboard-list'
            ],
            [
                'label' => 'Approved',
                'route' => 'tutors',
                'pattern' => 'tutor*',
                'icon' => 'fa-clipboard-check'
            ],
            [
                'label' => 'Denied',
                'route' => 'tutors',
                'pattern' => 'tutor*',
                'icon' => 'fa-circle-xmark'
            ],
        ]
    ],
    //student pspd
    [
        'title' => 'Logbook',
        'roles' => ['student'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Student Logbook',
                'route' => 'student.attendance.index',
                'pattern' => 'student/attendance*',
                'icon' => 'fa-calendar-alt'
            ],
        ]
    ],

];
