<?php

return [
    [
        'title' => null,
        'key' => 'dashboard',
        'roles' => ['admin', 'lecturer', 'student', 'koordinator'],
        'context' => ['pssk'],
        'items' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard.pssk',
                'pattern' => 'dashboard',
                'icon' => 'fa-chart-line',
            ],
        ]
    ],
    [
        'title' => null,
        'key' => 'dashboard',
        'roles' => ['admin', 'lecturer', 'student', 'koordinator'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard.pspd',
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
                'route' => 'pssk.admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-user-graduate'
            ],
            [
                'label' => 'Lecturers',
                'route' => 'pssk.admin.users.index',
                'params' => ['type' => 'lecturer'],
                'pattern' => 'admin/users/lecturer*',
                'icon' => 'fa-chalkboard-teacher'
            ],
            [
                'label' => 'Admin',
                'route' => 'pssk.admin.users.index',
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
                'route' => 'pssk.admin.semester.index',
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

    // [
    //     'title' => 'Attendance',
    //     'roles' => ['student'],
    //     'context' => ['pssk'],
    //     'items' => [
    //         [
    //             'label' => 'Previous Attendance',
    //             'route' => 'student.attendance.index',
    //             'pattern' => 'student/attendance*',
    //             'icon' => 'fa-calendar-alt'
    //         ],
    //     ]
    // ],
    //PSPD
    //admin/lecturer pspd
    [
        'title' => 'User Management',
        'roles' => ['admin'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Students',
                'route' => 'pspd.admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-user-graduate'
            ],
            [
                'label' => 'Lecturers',
                'route' => 'pspd.admin.users.index',
                'params' => ['type' => 'lecturer'],
                'pattern' => 'admin/users/lecturer*',
                'icon' => 'fa-chalkboard-teacher'
            ],
        ],
    ],
    [
        'title' => 'Master Data',
        'roles' => ['admin'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Rumah Sakit',
                'route' => 'rumah-sakit.index',
                'pattern' => 'rumah-sakit*',
                'icon' => 'fa-hospital'
            ],
            [
                'label' => 'Stase',
                'route' => 'stase.index',
                'pattern' => 'stase*',
                'icon' => 'fa-stethoscope'
            ],
            [
                'label' => 'Kepaniteraan',
                'route' => 'kepaniteraan.index',
                'pattern' => 'kepaniteraan*',
                'icon' => 'fa-briefcase-medical'
            ],
        ]
    ],
    [
        'title' => 'Monitoring',
        'roles' => ['admin'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Logbook Mahasiswa',
                'route' => 'pspd.admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-book-medical'
            ],
            [
                'label' => 'Rekap Kepaniteraan',
                'route' => 'pspd.admin.users.index',
                'params' => ['type' => 'student'],
                'pattern' => 'admin/users/student*',
                'icon' => 'fa-file-medical'
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
                'route' => 'logbook.index',
                'pattern' => 'logbook*',
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
                'label' => 'Input Logbook',
                'route' => 'student-logbook.index',
                'pattern' => 'logbook*',
                'icon' => 'fa-calendar-alt'
            ],
        ]
    ],
    [
        'title' => 'Kepaniteraan',
        'roles' => ['student'],
        'context' => ['pspd'],
        'items' => [
            [
                'label' => 'Kepaniteraan Aktif',
                'route' => 'student.attendance.index',
                'pattern' => 'student/attendance*',
                'icon' => 'fa-calendar-alt'
            ],
            [
                'label' => 'Riwayat Kepaniteraan',
                'route' => 'student.attendance.index',
                'pattern' => 'student/attendance*',
                'icon' => 'fa-calendar-alt'
            ],
        ]
    ],

];
