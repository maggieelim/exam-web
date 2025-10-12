<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('getActiveSidebarTitle')) {
  function getActiveSidebarTitle()
  {
    $map = [
      'admin/users/student*' => 'Students',
      'admin/users/lecturer*' => 'Lecturers',
      'admin/users/admin*' => 'Admin',

      'courses*' => 'Manage Courses',

      'exams/upcoming*' => 'Upcoming Exam',
      'exams/ongoing*' => 'Ongoing Exams',
      'exams/previous*' => 'Previous Exam',

      'lecturer/ungraded*' => 'Ungraded',
      'lecturer/published*' => 'Published',

      'student/exams/upcoming*' => 'Upcoming Exam',
      'student/exams/previous*' => 'Previous Exam',
      'student/results*' => 'Exam Results',

      'profile' => 'Profile',
    ];

    foreach ($map as $pattern => $title) {
      if (Request::is($pattern)) {
        return $title;
      }
    }

    return 'Dashboard';
  }
}
