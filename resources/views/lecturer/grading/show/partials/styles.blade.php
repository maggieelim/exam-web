<style>
  .nav-pills .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    padding: 12px 24px;
    transition: all 0.3s ease;
  }

  .nav-pills .nav-link.active {
    font-weight: 600;
    border-bottom: 3px solid #cb0c9f;
    background: white;
    color: #cb0c9f;
  }

  .nav-pills .nav-link:hover:not(.active) {
    background-color: #f8f9fa;
    color: #cb0c9f;
  }

  .nav-pills {
    border-bottom: 1px solid #dee2e6;
    background: white;
  }

  .quick-stat-card {
    transition: transform 0.2s ease;
  }

  .quick-stat-card:hover {
    transform: translateY(-2px);
  }

  .chart-container {
    position: relative;
    height: 250px;
    width: 100%;
  }
</style>