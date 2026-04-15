<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ulkeler extends Model
{
	protected $table = 'ulke';
    protected $fillable = [
        'binary_kod', 'uclu_kod',  'ulke_adi', 'telefon_kodu' ];
}
