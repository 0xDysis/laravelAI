<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'order_id', // Assuming you have an 'order_id' field
        'customer_name', // Assuming you have a 'customer_name' field
        'order_total', // Assuming you have an 'order_total' field
        // ... Add other actual column names here
    ];

    // Uncomment the following line if your table does not have 'created_at' and 'updated_at'
    // public $timestamps = false;

    // Uncomment and set this if your table name is not 'orders'
    // protected $table = 'your_custom_table_name';

    // Define relationships (if any)
    // public function relatedModel()
    // {
    //     return $this->belongsTo(RelatedModel::class);
    // }
}

