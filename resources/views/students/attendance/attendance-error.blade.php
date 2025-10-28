<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow text-center">
                    <div class="card-body p-5">
                        <div class="text-danger mb-4">
                            <i class="fas fa-exclamation-triangle fa-5x"></i>
                        </div>
                        <h3 class="text-danger mb-3">Attendance Submission Failed</h3>
                        <p class="text-muted mb-4">
                            {{ session('error') ?? 'An error occurred while processing your attendance.' }}
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="javascript:history.back()" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Go Back
                            </a>
                            <a href="#" class="btn btn-primary" onclick="window.close()">
                                <i class="fas fa-times me-2"></i>
                                Close
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
