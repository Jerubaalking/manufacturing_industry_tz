<?php

namespace App\Http\Controllers;
use App\Exports\ExportSuppliers;
use App\Imports\SuppliersImport;
use App\Supplier;
use Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;
use Yajra\DataTables\DataTables;
use App\Models\SalesModel;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $empo=DB::table('employee')
                ->get();

        $product=DB::table('products')
                    //->whereNotIn('stock',[0])
                     ->get();

        $account=DB::table('account')
                    ->where('account_group','=','Income')
                     ->get();
        $close_task=DB::table('employee')
                     ->join('task','task.empoyee_id','=','employee.id')
                     ->select('task.*','employee.first_name','employee.employee_number')
                     ->where('amount_due','=','0')
                     ->get();
    
        return view('task.index',compact('empo','product','account','close_task'));
          

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // if(request()->ajax()){
            $validator = Validator::make($request->all(), [
                "price.*" => 'required|integer|min:1',
            ]);
            if ($validator->fails()) 
            { 
                return response()->json([
                    'error'    => true,
                    'message'    => 'This Task is invalid'
                ]);

            }
            
            $date=Carbon::now()->format('Y-m-d');
            $item_name=$request->item_name;
            $date_in=$request->date_in;
            $qty=$request->qty;
            $price=$request->price;
            $amt=$request->amt;
            $subtotal=$request->sub_total;
            $supplier_id=$request->supplier_id;
            $product_id=$request->product_id;
            $return_qty='0';
            $return_price='0';
            $return_amt='0';
        
        //  $prove="Not approved";
        $outside = array();
        $recordDate= DB::table('task')->where('empoyee_id', '=', $supplier_id)->where('created_at', '=', $date_in)->orderBy('id', 'DESC')->first();
        //    dump($recordDate);
        if(!$recordDate)
            {
                $record= DB::table('task')->orderBy('id', 'DESC')->first();
                    if(!$record){
                        $nextTask = 'TASK'.'-0001';
                    }else{
                        
                        $expNum = explode('-', $record->task_number);
                        $increments=($expNum[1]+1);
                        //increase 1 with last invoice number
                            $nextTask = $expNum[0].'-'.sprintf("%04d",$increments);
                        
                    }
                    $form_datas=array(

                        'sub_total'=>round($subtotal,2),
                        'empoyee_id'=>$supplier_id,
                        'created_at'=>$date,
                        'amount_paid'=>'0',
                        'amount_due'=>$subtotal,
                        'task_number'=>$nextTask,
                        'returned'=>$return_amt

                    
                    );
                array_push($outside, $form_datas);
                $get_id=DB::table('task')->insertGetId($form_datas);
            }else{
                
                $record= DB::table('task')->where('id', '=', $recordDate->id)->orderBy('id', 'DESC')->first();
                //add value to task
                
                    $form_datas=array(

                        'sub_total'=>round($record->sub_total+$subtotal,2),
                        'empoyee_id'=>$supplier_id,
                        'created_at'=>$date,
                        'amount_paid'=>$record->amount_paid,
                        'amount_due'=>$record->amount_due+$subtotal,
                        'task_number'=>$record->task_number,
                        'returned'=>$record->returned+$return_amt
                    
                ); 
                DB::table('task')
                ->where('id', $record->id)
                ->update($form_datas);
                $get_id=$record->id;
            }
            if($qty != null){
                for($count=0; $count < sizeof($qty);$count++){
                    $form_data[]= array(
                    'task_id'=>$get_id,
                    'product_id' =>$product_id[$count],
                    'qty'  =>$qty[$count],
                    'price'  =>round($price[$count],2),
                    'amt'=>round($price[$count]*$qty[$count],2),
                    'return_qty'  =>$return_qty,
                    'return_price'  =>$return_price,
                    'return_amt'  =>$return_amt,
                    'created_at'  =>$date_in,
                    
                    );
            //     //    DB::table('products')->where('id', $product_id[$count])->decrement('stock', $qty[$count]);
                }
                
                DB::table('sales')->insert($form_data);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Information successfuly added'
                ]);

            }else{
                return response()->json([
                    'success'    => false,
                    'message'    => 'Information not added'
                ]);
            }
        
            
        //  SalesModel::create($form_data);   
        // } 
       
       }
       

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        if(request()->ajax()){
        $data=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->select('task.*','employee.first_name','employee.employee_number','sales.qty','sales.amt',
        'products.product_name','sales.price','products.stock','sales.product_id')
        ->where('task.id','=',$id)
        ->get();
          if($data){  
            return response()->json(['data' => $data]);
            }
            return view('casual.staffing.index',comapact('data'));
            }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
             //
             $date=Carbon::now()->format('Y-m-d');
             $item_name=$request->item_name;
             $date_in=$request->date_in;
             $qty=$request->qty;
             $price=$request->price;
             $id=$request->id;
             $amt=$request->amt;
             $subtotal=$request->sub_total;
             $supplier_id=$request->supplier_id;
             $product_id=$request->product_id;
           
            //  $prove="Not approved";
            
             $form_datas=array(
                 'sub_total'=>round($subtotal,2),
                 'empoyee_id'=>$supplier_id,
                 'created_at'=>$date,
                 'amount_paid'=>'0',
                 'amount_due'=>'0'
                
            );
         
            DB::table('task')
            ->where('id','=',$id)->update($form_datas);
            
             $x=DB::table('sales')
             ->where('task_id','=',$id)
             ->select('product_id','qty')
             ->get()
             ->toArray();
              
             foreach($x as $x){
                 $ids[]=$x->product_id;
                 $qtys[]=$x->qty;
                  for($count=0; $count < count($ids);$count++){
                    $xx= DB::table('products')->whereIn('id',[$ids[$count]])
                    ->increment('stock',$qtys[$count]);
                    }
                }

                for($count=0; $count < count($qty);$count++){
                    $form_data[]= array(
                     'task_id'=>$id,
                     'product_id' =>$product_id[$count],
                     'qty'  =>$qty[$count],
                     'price'  =>round($price[$count],2),
                     'amt'=>round($price[$count]*$qty[$count],2),
                     'created_at'  =>$date_in,
                    
                    );
                    DB::table('products')->where('id', $product_id[$count])->decrement('stock', $qty[$count]);
                }
           
               
            DB::table('sales')->where('task_id','=',$id)->delete();
    
            DB::table('sales')->insert($form_data);
           return response()->json([
               'success'    => true,
               'message'    => 'Information Updated'
           ]);   
               
           }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
     

         $x=DB::table('sales')
         ->where('task_id','=',$id)
         ->select('product_id','qty')
         ->get()
         ->toArray();
             foreach($x as $x){
               $ids[]=$x->product_id;
               $qtys[]=$x->qty;
             
            }  

           $task=  DB::table('task')
            ->where('id','=',$id)->delete();
            if($task){
                for($count=0; $count < count($ids);$count++){
                    $xx= DB::table('products')->whereIn('id',[$ids[$count]])
                    ->increment('stock',$qtys[$count]);
                 }

            }
    }
    public function apiTask($start, $end)
    {
        if(request()->ajax()){
        
        info($start);
        info($end);
        $tasks=DB::table('task')
        ->whereDate('task.created_at','>=',$start)
        ->whereDate('task.created_at','<=',$end)
        ->join('employee', 'employee.id', '=', 'task.empoyee_id')
        ->join('sales', 'task.id', '=', 'sales.task_id')
        ->where('task.amount_paid','<', 'task.sub_total')
        ->where('task.amount_due','>', 0)
        ->select('task.id','sales.id as sales_id','task.task_number','task.empoyee_id','task.sub_total', 'task.amount_paid', 'task.returned','task.demage_cost', 'task.amount_due','task.created_at','task.updated_at', 'employee.first_name','employee.last_name')
        ->orderBy('task.created_at', 'DESC')
        ->get();
        $task = array();
        foreach ($tasks as $keys => $tasky) {
            # code...
            
            $task1 = array();
            if(sizeof($task)<=0){
                array_push($task, $tasky);
            }else{
                
                if(array_search($tasky->task_number, array_column($task, 'task_number')) !== FALSE){
                    $key = array_search($tasky->task_number, array_column($task, 'id'));
                    if(array_search($tasky->task_number, array_column($task, 'task_number')) === FALSE){
                        if( $task[$key]->task_number == $tasky->task_number){
                            $task[$key]->sub_total +=$tasky->sub_total; 
                            $task[$key]->amount_paid +=$tasky->amount_paid;
                            $task[$key]->returned +=$tasky->returned; 
                            $task[$key]->demage_cost +=$tasky->demage_cost; 
                            $task[$key]->amount_due +=$tasky->amount_due; 
                        }else{
                            
                            array_push($task, $tasky);
                        }
                    }else{
                       
                    }
                }else{
                    
                    array_push($task, $tasky);
                }
                
            }
            
            
        }
        // $key = array_search($tasky->task_number, array_column($tasks, 'task_number'), false);
        
            
        }
        
        // You have to create a link option to view account
        // info($task);
        if(Auth::user()->role=="Superadministrator"){
        return Datatables::of($task)
            ->addColumn('action', function($task){
                info($task->id);
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                        <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white"  id="'.$task->empoyee_id.'" test="'.$task->id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>
                        
                       <li><a href="#" id="'.$task->id.'" class="btn btn-warning btn-xs demageForm" ><i class="glyphicon glyphicon-plus" style="color:white"></i> add damaged</a></li>
                       <li><a onclick="deleteData('. $task->id .')" id="'.$task->id.'" class="btn btn-danger btn-xs" style="color:white"><i class="glyphicon glyphicon-trash" style="color:white"></i> Delete</a></li>
                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More Details</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format((intVal($task->sub_total)-intVal($task->demage_cost))-intVal($task->amount_paid),2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
        }else{
            return Datatables::of($task)
            ->addColumn('action', function($task){
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                   <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" id="'.$task->id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>

                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More Details</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format((intVal($task->sub_total)-intVal($task->demage_cost))-intVal($task->amount_paid),2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
        }
    }
    public function account(Request $request, $id, $start, $end){
        info($id);
        $tasks=DB::table('employee')
        ->where('empoyee_id', '=', $id)
        ->join('task', 'employee.id', '=', 'task.empoyee_id')
        ->whereDate('task.created_at','>=',$start)
        ->whereDate('task.created_at','<=',$end)
        ->get();
        return json_encode($tasks);
        // return view('task.details',compact('tasks'));
    }
    public function apiAccountsTask(Request $request, $start, $end)
    {
        $tasks=DB::table('task')
        ->whereDate('task.created_at','>=',$start)
        ->whereDate('task.created_at','<=',$end)
        ->join('employee', 'employee.id', '=', 'task.empoyee_id')
        ->select('task.*', 'employee.first_name', 'employee.last_name')
        ->get();

        $dates = DB::table('task')
        ->groupBy('created_at')
        ->whereNotIn('amount_due',['0'])
        ->select('created_at')
        ->get();
        $task = array();
        $employees = DB::table('employee')
        ->get();
        // info($employees);
        // info($dates);
        foreach ($employees as $employeekey => $employee) {
            
            $ttask = array();
            foreach ($tasks as $taskKey => $task1) {
               if($task1->empoyee_id == $employee->id){
                    if(sizeof($ttask)<= 0){
                        $task1->first_name = $task1->first_name.' '.$task1->last_name; 
                        array_push($ttask, $task1);
                    }else{
                        // info($ttask);
                        if($ttask[0]->demage_cost == NULL){
                            $ttask[0]->id = $task1->id; 
                            $ttask[0]->first_name = $task1->first_name.' '.$task1->last_name; 
                            $ttask[0]->returned = intVal($task1->returned);
                            $ttask[0]->demage_cost = intVal($task1->demage_cost);
                            $ttask[0]->amount_due += intVal($task1->amount_due);
                            $ttask[0]->amount_paid += intVal($task1->amount_paid);
                            $ttask[0]->sub_total += intVal($task1->sub_total); 
                            $ttask[0]->created_at = $task1->created_at; 
                        }else{
                            $ttask[0]->id = $task1->id; 
                            $ttask[0]->created_at = $task1->created_at; 
                            $ttask[0]->first_name = $task1->first_name.' '.$task1->last_name; 
                            $ttask[0]->returned = intVal($task1->returned);
                            $ttask[0]->demage_cost += intVal($task1->demage_cost);
                            $ttask[0]->amount_due += intVal($task1->amount_due);
                            $ttask[0]->amount_paid += intVal($task1->amount_paid);
                            $ttask[0]->sub_total += intVal($task1->sub_total); 
                            $ttask[0]->created_at = $task1->created_at;
                        }
                    }
               }
            }
            foreach($ttask as $tt){
                $payments=DB::table('receive_sales')
                ->where('receive_sales.employee_id', $tt->empoyee_id)
                ->select('receive_sales.id','receive_sales.task_id', 'receive_sales.created_at', 'receive_sales.amount')
                ->get();
                
                // dump($tt->empoyee_id);
                // foreach ($payments as $v) {
                //     # code...
                //     // dump($v);
                //     if($v->task_id == $tt->id){
                //      break;   
                //     }else{
                //         $tt->amount_paid += $v->amount;
                //     }

                // }

                
                
                if($tt->amount_paid > $tt->sub_total){
                    $tt->amount_due = 0;
                }else{
                    $tt->amount_due = intVal($tt->sub_total-($tt->amount_paid+$tt->demage_cost+$tt->returned));
                }
                if($tt->amount_due < 0){
                    $tt->amount_paid += $tt->amount_due; 
                    $tt->amount_due = 0;
                }
                array_push($task, $tt);
            }
            // info($task);
            
        }
        // You have to create a link option to view account
        // info($task);
        if(Auth::user()->role=="Superadministrator"){
        return Datatables::of($task)
            ->addColumn('action', function($task){
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                   <!--<li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" task="'.$task->id.'" id="'.$task->empoyee_id.'" ><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>-->
                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More</a></li>
                       <li><a href="#" class="btn btn-warning btn-xs block" style="color:white" id="'.$task->empoyee_id.'"><i class="fa fa-money" style="color:white"></i>Block Account</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format($task->amount_due,2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
        }else{
            return Datatables::of($task)
            ->addColumn('action', function($task){
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                   <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" id="'.$task->empoyee_id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>

                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More Details</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format($task->amount_due,2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
        }
    }
    public function apiClosedTask($start, $end)
    {
        $task=DB::table('task')
        ->join('employee','task.empoyee_id','=','employee.id')
        ->whereDate('task.created_at','>=',$start)
        ->whereDate('task.created_at','<=',$end)
        ->select('task.*','employee.first_name','employee.employee_number')
        // ->where('task.amount_paid','=','task.sub_total')
        ->where('amount_due','=','0')
        ->get();

        return Datatables::of($task)
            ->addColumn('action', function($task){
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                   <li>
                   <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" id="'.$task->empoyee_id.'" test="'.$task->id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>

                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More Details</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format($task->amount_due,2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
    }
    public function apiDamagedTask($start, $end)
    {

        $task=DB::table('task')
        ->join('employee','task.empoyee_id','=','employee.id')
        ->whereDate('task.created_at','>=',$start)
        ->whereDate('task.created_at','<=',$end)
        ->select('task.*','employee.first_name','employee.employee_number')
        ->whereNotIn('demage_cost',['0'])
        ->get();
        info($task);

        return Datatables::of($task)
            ->addColumn('action', function($task){
                return '
                <div class="btn-group" style="width:100%">
                   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Action <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                   <li>
                   <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white"  id="'.$task->empoyee_id.'" test="'.$task->task_number.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>

                       <li><a href="task_info/'.$task->id .'" class="btn btn-success btn-xs more_details" style="color:white" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i>More Details</a></li>
                   </ul>
               </div> ';
            })
            ->editColumn('demage_cost', function($task){
     
                return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
            })
            ->editColumn('amount_due', function($task){
     
                return '<div class="text-danger">'.number_format($task->amount_due,2).'</div>';
            })
            ->editColumn('amount_paid', function($task){
     
                return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
            })
            ->editColumn('returned', function($task){
     
                return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
            })
            ->editColumn('sub_total', function($task){
     
                return '<div class="text-primary">'.number_format(intVal($task->sub_total),2).'</div>';
            })
            ->escapeColumns([])
            ->rawColumns(['action'])->make(true);
    }

//                       <li><a href="#" class="btn btn-info btn-xs view" style="color:white" id="'.$task->id.'" ><i class="glyphicon glyphicon-eye-open" style="color:white"></i> Show</a></li>
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stockReturn($id)
    {
        //
        // $datas = DB::table('sales')
        // ->where('task_id', '=', $id)
        // ->get();
        
        if(request()->ajax()){
        $datas=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->select('sales.*','employee.first_name','employee.employee_number','employee.phone','employee.last_name','sales.qty','task.empoyee_id','sales.amt',
        'products.product_name','sales.price','products.stock','sales.product_id')
        ->where('task.id','=',$id)
        ->get();
        $data = array();
        // $dataz = array($datas);
        foreach ($datas as $key => $value) {
            # code...
            $damages = DB::table('product_demage')
            ->where('sales_id', $value->id)
            ->get();
            $value->damage_qty = 0;
            foreach ($damages as $key1 => $value1) {
                # code...
                if($value1->sales_id == $value->id){
                     $value->$damage_qty += $value1->qty;
                    break;
                }
                break;
            }
            array_push($data, $value);
            
            // dump($value);
        }
        // ->join('task','task.empoyee_id','=','employee.id')
        // ->join('sales','task.id','=','sales.task_id')
        // ->join('product_demage','task.id','=','product_demage.task_id')
        // ->join('products','sales.product_id','=','products.id')
        // ->select('product_demage.*','employee.first_name','employee.employee_number','employee.phone','employee.last_name','task.empoyee_id',
        // 'products.product_name', 'sales.id as sales_id','task.created_at')
        // ->where('task.id','=',$id)
        info($data);
            return response()->json(['data' => $data]);
            
            // return view('casual.staffing.index', compact('data'));
        // }
    }
    }

        /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function amount_due($id, $start, $end)
    {
        //
        if(request()->ajax()){
        $datas=DB::table('task')
        ->where('empoyee_id','=',$id)
        ->whereBetween('created_at', [$start, $end])
        ->get();
       
        $data = array();
        foreach ($datas as $key => $value) {
            if(sizeof($data)<=0){
                array_push($data, $value);
            }else{
                $data[0]->sub_total += $value->sub_total;
                $data[0]->amount_paid += $value->amount_paid;
                $data[0]->returned += $value->returned;
                $data[0]->demage_cost += $value->demage_cost;
            }
        }

        // foreach ($datas as $key2 => $value2) {
        //     $payments=DB::table('receive_sales')
        //     ->where('employee_id','=',$value2->empoyee_id)
        //     ->where('task_id','=',$value2->id)
        //     ->whereBetween('created_at', [$start, $end])
        //     ->get();
        //     foreach ($payments as $key1 => $value1) {

        //             if($value2->id == $value1->task_id){
        //                 info($value1->task_id);
        //                 // break;
        //             }else{
        //                 info($value1->task_id);
        //                 // info($value2->id);
        //                 // $data[0]->amount_paid += $value1->amount;
        //                 break;
        //             } 
        //     }
        // }
        
        if((intVal($data[0]->amount_paid) + (intVal($data[0]->demage_cost)+ intVal($data[0]->returned))) > intVal($data[0]->sub_total) ){
            $data[0]->amount_due = 0;
        }else{
        $data[0]->amount_due = intVal($data[0]->sub_total) - (intVal($data[0]->amount_paid) + (intVal($data[0]->demage_cost)+ intVal($data[0]->returned)));
        }
        
        
        info($data);
            return response()->json(['data' => $data[0]]);
            
        
        }
    }
/**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function task_info($id){
        $data=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->select('task.*','employee.first_name','employee.employee_number','sales.qty','sales.amt',
        'products.product_name','sales.price','products.stock','employee.last_name',
        'sales.product_id','return_qty','return_price','return_amt')
        ->where('task.id','=',$id)
        ->get();
        $pay=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('receive_sales','receive_sales.task_id','=','task.id')
        ->select('receive_sales.*','employee.first_name',
        'employee.employee_number','task.task_number')
        ->where('task.id','=',$id)
        ->get();
        $return_task=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('product_demage','task.id','=','product_demage.task_id')
        ->join('products','products.id','=','product_demage.product_id')
        ->select('product_demage.*','employee.first_name','employee.employee_number',
        'products.product_name','products.stock',
        'task.task_number')
        ->where('product_demage.task_id','=',$id)
        ->get();
        $demage_product=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','sales.task_id','=','task.id')
        ->join('products','products.id','=','sales.product_id')
        ->join('product_demage','products.id','=','product_demage.product_id')
        ->select('product_demage.*','employee.first_name','employee.employee_number',
        'products.product_name','products.stock',
        'task.task_number')
        ->where('product_demage.task_id','=',$id)
        ->get();

        $empo=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->where('task.id','=',$id)
        ->get();
        $emp_number=$empo[0]->employee_number;

        $close_task=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->where('employee.employee_number','=',$emp_number)
        ->where('task.amount_due','=','0')
        ->get();
        info($pay);
        return view('task.task_info',compact('data','pay','empo','return_task','close_task','demage_product','id'));
        
    }

    public function account_info($id){
        $data=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->select('task.*','employee.first_name','employee.employee_number','sales.qty','sales.amt',
        'products.product_name','sales.price','products.stock','employee.last_name',
        'sales.product_id','return_qty','return_price','return_amt')
        ->where('employee.id','=',$id)
        ->get();
        $pay=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('receive_sales','receive_sales.task_id','=','task.id')
        ->join('sales','sales.task_id','=','task.id')
        ->select('receive_sales.*','employee.first_name',
        'employee.employee_number','task.task_number', 'sales.qty')
        ->where('employee.id','=',$id)
        ->get();
        $return_task=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('product_demage','task.id','=','product_demage.task_id')
        ->join('products','products.id','=','product_demage.product_id')
        ->select('product_demage.*','employee.first_name','employee.employee_number',
        'products.product_name','products.stock',
        'task.task_number')
        ->where('product_demage.employee_id','=',$id)
        ->get();
        $demage_product=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('products','products.id','=','stock_return.product_id')
        ->select('employee.first_name','employee.employee_number',
        'products.product_name','products.stock',
        'task.task_number')
        ->where('employee.id','=',$id)
        ->get();

        $empo=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->where('employee.id','=',$id)
        ->get();
        $emp_number=$empo[0]->employee_number;

        $close_task=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->where('employee.employee_number','=',$emp_number)
        ->where('task.amount_due','=','0')
        ->get();

        return view('task.task_info',compact('data','pay','empo','return_task','close_task','demage_product','id'));
        
    }
 /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */   
    public function exportTask(Request $request)
    {
        $datesArray = explode('-', $request->date_range);
        $from = Carbon::create(intVal(explode('/',$datesArray[0])[2]),intVal(explode('/',$datesArray[0])[0]),intVal(explode('/',$datesArray[0])[1]));
        $to = Carbon::create(intVal(explode('/',$datesArray[1])[2]),intVal(explode('/',$datesArray[1])[0]),intVal(explode('/',$datesArray[1])[1]));
        info($from);
        $product_out=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->whereBetween('task.created_at',array($from,$to))
        ->select('sales.*','task.demage_cost','task.returned','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone','employee.last_name',)
        ->get();
        info($product_out);
        $count=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->whereBetween('task.created_at',array($from,$to))
        ->select('sales.*','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone')
        ->count();
        $sum_qty=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
       
        ->sum('sales.qty');

        $sum_amt=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
        ->sum('sales.amt');

        $sum_return_qty=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')

        ->whereBetween('task.created_at',array($from,$to))
       
        ->sum('sales.return_qty');

        $sum_return_amt=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->whereBetween('task.created_at',array($from,$to))
        ->select('sales.*','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone')
        ->sum('sales.return_amt');

     
        $sum_return=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
        ->sum('task.returned');
        
        $sum_demage=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
        ->sum('task.demage_cost');


        $sum_sub=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
        ->sum('sales.amt');

        $sum_recive=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->whereBetween('task.created_at',array($from,$to))
        ->sum('task.amount_paid');
        
        $sum_due= $sum_sub -($sum_recive + $sum_demage + $sum_return);



        $pdf = PDF::loadView('task.export_task',compact('count','product_out','from','to','sum_qty','sum_amt',
        'sum_return_qty','sum_return_amt','sum_return','sum_due','sum_recive','sum_sub','sum_due','sum_demage'));
        
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('supplier.pdf');
    }


     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */   
    public function single_report($id)
    {
        
    
        $product_out=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('sales.*','products.product_name','task.created_at','employee.employee_number',
        'employee.first_name','employee.last_name',
        'employee.phone')
        ->get();
        $x=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('stock_return','task.id','=','stock_return.task_id')
        ->join('products','stock_return.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('stock_return.*','products.product_name','task.created_at','employee.employee_number',
        'employee.first_name','employee.last_name',
        'employee.phone')
        ->get();

        $demage=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('product_demage','task.id','=','product_demage.task_id')
        ->join('products','product_demage.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('product_demage.*','products.product_name','task.created_at','employee.employee_number',
        'employee.first_name','employee.last_name',
        'employee.phone')
        ->get();

        $count=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->join('products','sales.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('sales.*','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone')
        ->count();
        $sum_qty=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
     
        ->where('task.id','=',$id)
       
        ->sum('sales.qty');

        $sum_amt=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('sales','task.id','=','sales.task_id')
        ->where('task.id','=',$id)
        ->sum('sales.amt');

        $sum_return_qty=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('stock_return','task.id','=','stock_return.task_id')

        ->where('task.id','=',$id)
       
        ->sum('stock_return.qty');

        $sum_return_amt=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('stock_return','task.id','=','stock_return.task_id')
        ->join('products','stock_return.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('stock_return.*','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone')
        ->sum('stock_return.amt');

        $sum_demage_qty=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('product_demage','task.id','=','product_demage.task_id')
        ->where('task.id','=',$id)
        ->sum('product_demage.qty');

        $sum_demage_amt=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
        ->join('product_demage','task.id','=','product_demage.task_id')
        ->join('products','product_demage.product_id','=','products.id')
        ->where('task.id','=',$id)
        ->select('product_demage.*','products.product_name','task.created_at','employee.employee_number','employee.first_name',
        'employee.phone')
        ->sum('product_demage.amt');

        $sum_return=DB::table('employee')
        ->join('task','task.empoyee_id','=','employee.id')
    
        ->where('task.id','=',$id)
      
        ->sum('task.returned');

        $sum_due=DB::table('task')
        ->where('id','=',$id)
   
        ->sum('amount_due');

        $sum_sub=DB::table('task')
        ->where('id','=',$id)
        ->sum('sub_total');

        $sum_recive=DB::table('task')
        ->where('id','=',$id)
      
        ->sum('amount_paid');



        $pdf = PDF::loadView('task.single_report',compact('count','product_out','sum_qty','sum_amt',
        'sum_return_qty','sum_return_amt','sum_return','sum_due','demage',
        'sum_recive','sum_sub','sum_due','x','sum_demage_qty','sum_demage_amt'));
        
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('supplier.pdf');
    }

    public function exportProductKeluar($id)
    {
        $product_keluar = Product_Keluar::findOrFail($id);
        $pdf = PDF::loadView('product_keluar.productKeluarPDF', compact('product_keluar'));
        return $pdf->download($product_keluar->id.'_product_keluar.pdf');
    }

public function infoApi($id){
    $task=DB::table('employee')
    ->join('task','task.empoyee_id','=','employee.id')
    ->join('sales','task.id','=','sales.task_id')
    ->join('products','sales.product_id','=','products.id')
    ->select('task.*','employee.first_name','employee.last_name','employee.employee_number','sales.id as sales_id','sales.qty','sales.amt',
    'products.product_name','sales.price','products.stock',
    'sales.product_id','return_qty','return_price','return_amt')
    ->where('task.id','=',$id)
    ->get();
    return Datatables::of($task)
        ->addColumn('action', function($task){
            return '
            <div class="btn-group" style="width:100%">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   Action <span class="caret"></span>
               </button>
               <ul class="dropdown-menu">
           
               <li><a onclick="returnForm('. $task->sales_id .')" class="btn btn-danger btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Return Stock</a></li>
                    <li><a onclick="demageForm('. $task->sales_id .')" class="btn btn-primary btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Demage Products</a></li>
                
               </ul>
           </div> ';
        })
        ->editColumn('demage_cost', function($task){
 
            return '<div class="text-warning">'.number_format($task->demage_cost,2).'</div>';
        })
        ->editColumn('amount_due', function($task){
 
            return '<div class="text-danger">'.number_format($task->amount_due,2).'</div>';
        })
        ->editColumn('amount_paid', function($task){
 
            return '<div class="text-success">'.number_format($task->amount_paid,2).'</div>';
        })
        ->editColumn('returned', function($task){
 
            return '<div class="text-primary">'.number_format($task->returned,2).'</div>';
        })
        ->editColumn('sub_total', function($task){
 
            return '<div class="text-primary">'.number_format($task->sub_total,2).'</div>';
        })
        ->escapeColumns([])
        ->rawColumns(['action'])->make(true);
    }

   public function demageApi($id){
    $demage_product=DB::table('employee')
    ->join('task','task.empoyee_id','=','employee.id')
    ->join('product_demage','task.id','=','product_demage.task_id')
    ->join('products','products.id','=','product_demage.product_id')
    ->select('product_demage.*','employee.first_name','employee.employee_number',
    'products.product_name','products.stock',
    'task.task_number')
    ->where('product_demage.task_id','=',$id)
    ->get();
    return Datatables::of($demage_product)
    ->addColumn('action', function($demage_product){
        return '
        <div class="btn-group" style="width:100%">
           <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               Action <span class="caret"></span>
           </button>
           <ul class="dropdown-menu">
       
           <li><a onclick="returnForm('. $demage_product->id .')" class="btn btn-danger btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Return Stock</a></li>
                <li><a onclick="demageForm('. $demage_product->id .')" class="btn btn-primary btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Demage Products</a></li>
               <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" id="'.$demage_product->id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>


           </ul>
       </div> ';
    })
 
    ->escapeColumns([])
    ->rawColumns(['action'])->make(true);
   }

   public function returnApi($id){
    $return_task=DB::table('employee')
    ->join('task','task.empoyee_id','=','employee.id')
    ->join('stock_return','task.id','=','stock_return.task_id')
    ->join('products','products.id','=','stock_return.product_id')
    ->select('stock_return.*','employee.first_name','employee.employee_number',
    'products.product_name','products.stock',
    'task.task_number')
    ->where('stock_return.task_id','=',$id)
    ->get();
    return Datatables::of($return_task)
    // ->addColumn('action', function($return_task){
    //     return '
    //     <div class="btn-group" style="width:100%">
    //        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    //            Action <span class="caret"></span>
    //        </button>
    //        <ul class="dropdown-menu">
       
    //        <li><a onclick="returnForm('. $return_task->id .')" class="btn btn-danger btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Return Stock</a></li>
    //             <li><a onclick="demageForm('.$return_task->id .')" class="btn btn-primary btn-xs" style="color:white"><i class="fa fa-undo"  style="color:white"></i>Demage Products</a></li>
    //            <li><a href="#" class="btn btn-warning btn-xs pays" style="color:white" id="'.$return_task->id.'"><i class="fa fa-money" style="color:white"></i>Receive Payment</a></li>


    //        </ul>
    //    </div> ';
    // })
 
    ->escapeColumns([])
    ->rawColumns(['action'])->make(true);
   }
}
//        <li><a href="exportProforma/'.$task->id .'" class="btn btn-warning btn-xs pro_invoice" style="color:white" ><i class="fas fa-file-invoice" style="color:white"></i>Proforma Invoice</a></li>
//        <li><a onclick=" editForm('. $task->id .')" class="btn btn-success btn-xs" style="color:white"><i class="glyphicon glyphicon-pencil" style="color:white"></i> Edit</a></li>