<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubscriptionPayment extends Model {
    protected $fillable = ['subscription_id','user_id','amount','currency','transaction_id','session_key','validation_id','bank_transaction_id','payment_method','status','paid_at','gateway_response'];
    protected $casts = ['amount'=>'decimal:2','paid_at'=>'datetime','gateway_response'=>'array'];
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
