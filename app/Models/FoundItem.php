<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class FoundItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['staff_id','title','description','category_id','date_found','location_found','image','status'];
    protected $casts = ['date_found'=>'date'];
    public function staff(): BelongsTo { return $this->belongsTo(User::class, 'staff_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function claims(): HasMany { return $this->hasMany(Claim::class); }
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query->when($filters['q'] ?? null, fn($q,$term)=>$q->where(fn($i)=>$i->where('title','like',"%$term%")->orWhere('description','like',"%$term%")->orWhere('location_found','like',"%$term%")))
            ->when($filters['category_id'] ?? null, fn($q,$id)=>$q->where('category_id',$id))->when($filters['status'] ?? null, fn($q,$s)=>$q->where('status',$s))
            ->when($filters['from'] ?? null, fn($q,$d)=>$q->whereDate('date_found','>=',$d))->when($filters['to'] ?? null, fn($q,$d)=>$q->whereDate('date_found','<=',$d));
    }
}
