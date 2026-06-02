<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Claim extends Model
{
    protected $fillable = ['student_id','found_item_id','lost_item_id','status','claim_description','proof_image','reviewed_by','reviewed_at','review_note','pickup_instruction','released_by','released_at'];
    protected $casts = ['reviewed_at'=>'datetime','released_at'=>'datetime'];
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function foundItem(): BelongsTo { return $this->belongsTo(FoundItem::class); }
    public function lostItem(): BelongsTo { return $this->belongsTo(LostItem::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function releaser(): BelongsTo { return $this->belongsTo(User::class, 'released_by'); }
    public function scopeFiltered(Builder $query, array $filters): Builder
    { return $query->when($filters['q'] ?? null, fn($q,$term)=>$q->where('claim_description','like',"%$term%")->orWhereHas('foundItem', fn($i)=>$i->where('title','like',"%$term%"))->orWhereHas('student', fn($u)=>$u->where('name','like',"%$term%")))->when($filters['status'] ?? null, fn($q,$s)=>$q->where('status',$s)); }
}
