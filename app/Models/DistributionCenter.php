<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'acronym', 
        'name'
    ];
}
