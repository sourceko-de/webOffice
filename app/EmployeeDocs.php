<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Holiday
 * @package App\Models
 */
class EmployeeDocs extends Model
{
    // Don't forget to fill this array
    protected $fillable = [];

    protected $guarded = ['id'];
    protected $table =  'employee_docs';

    public function user(){
        return $this->belongsTo(User::class);
    }
}