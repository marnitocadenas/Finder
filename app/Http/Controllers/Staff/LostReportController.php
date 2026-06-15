<?php
namespace App\Http\Controllers\Staff;
use App\Http\Controllers\Controller; use App\Models\Category; use App\Models\LostItem; use Illuminate\Http\Request; use Illuminate\View\View;
class LostReportController extends Controller { public function index(Request $request): View { return view('items.lost-index',['role'=>'staff','personalView'=>false,'items'=>LostItem::with(['user','category'])->filtered($request->all())->latest()->paginate(15)->withQueryString(),'categories'=>Category::orderBy('name')->get()]); } public function show(LostItem $lostReport): View { return view('items.lost-show',['item'=>$lostReport->load(['user','category']),'role'=>'staff']); } }
