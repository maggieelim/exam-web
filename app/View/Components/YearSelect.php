<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class YearSelect extends Component
{
  public $name;
  public $selected;

  public function __construct($name, $selected = null)
  {
    // Kalau selected kosong/null → set default tahun sekarang
    $this->name = $name;
    $this->selected = $selected ?? date('Y');
  }

  public function render(): View|Closure|string
  {
    return view('components.year-select');
  }
}
