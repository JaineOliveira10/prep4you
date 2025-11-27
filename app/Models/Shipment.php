<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_date',
        'collection_date',
        'status',
        'name',
        'client_id',
        'distribution_center_id',
        'shipment_code',
        'imported_flag',
        'creation_date',
        'total_value',
        'total_items',
        'error_message'
    ];

    // Define os status válidos
    public const STATUS_PENDING = 'Pending';
    public const STATUS_IN_PREPARATION = 'In Preparation';
    public const STATUS_PACKED = 'Packed';
    public const STATUS_COLLECTED = 'Collected';
    public const STATUS_INVOICE_GENERATED = 'Invoice Generated';
    public const STATUS_PAID = 'Paid';
    public const STATUS_PRESENTS_ERRORS = 'Presents Errors';

    public static function getValidStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PREPARATION,
            self::STATUS_PACKED,
            self::STATUS_COLLECTED,
            self::STATUS_INVOICE_GENERATED,
            self::STATUS_PAID,
            self::STATUS_PRESENTS_ERRORS
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function distributionCenter()
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function pdfs()
    {
        return $this->hasMany(ShipmentPdf::class);
    }

    // Validar se o status é válido
    public function isValidStatus($status)
    {
        return in_array($status, self::getValidStatuses());
    }

    // Verificar se tem erros
    public function hasErrors()
    {
        return $this->status === self::STATUS_PRESENTS_ERRORS;
    }
}
