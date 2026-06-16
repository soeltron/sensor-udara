<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RelayControl extends Model
{
    protected $fillable = ['is_on', 'auto_mode'];
}