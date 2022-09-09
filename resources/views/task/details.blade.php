@extends('layouts.master')
@section('top')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">

<!-- daterange picker -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.1.0/css/buttons.dataTables.min.css">
<link rel="stylesheet"
    href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection
@section('content')
<div class="box" style="margin-top:30px;">
 
<div class="box-body">
                        <div class="table-responsive table-striped  table-responsive col-md-5"> 
                        <table  class="table  table-striped table-data2" >
                            <thead>
                            <h3 class="title-5 m-b-35" ><?php
                                            $task_number = '';
                                            $total_paid = 0;
                                            $method = '';
                                            $total_amount = 0;
                                            $total_due = 0;
                                            $name = '';
                                            foreach($tasks as $i){
                                            $task_number = $i->task_number;
                                            $total_paid += $i->amount_paid;
                                            $total_amount += $i->sub_total;
                                            $total_due += $i->amount_due;
                                            $name =$i->first_name;
                                            }
                                            echo $name;
                                        ?> Account status</h3>
                            <tr>
                                    <th></th>
                                
                                    <th></th>
                                
                            </tr>
                            </thead>
                            <tbody> 
                                    <tr>
                                        <td>Allocated Amount </td>
                                        <td>                                   
                                        <strong class="text-primary"><?php echo number_format($total_amount)?> /=</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Paid Amount </td>
                                        <td>                                   
                                        <strong class="text-dark"><?php echo number_format($total_paid)?> /=</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Outstanding Balance </td>
                                        <td>                                   
                                        <strong class="text-danger" style="opacity:0.7; color:red;"><?php echo number_format($total_amount-$total_paid)?> /=</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>                                   
                                        <button class="btn btn-sm btn-warning">block</button>
                                        <button class="btn btn-sm btn-danger">delete</button>
                                        <a href="/task" class="">back</a>
                                        </td>
                                    </tr>
                                    

                            </tbody>
                        
                        </table>
                        </div>
                    </div>
</div>
<!-- /.modal-dialog -->
@endsection

@section('bot')

<script src=" {{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }} "></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }} "></script>


    <!-- InputMask -->
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.js') }}"></script>
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.date.extensions.js') }}"></script>
    <script src="{{ asset('assets/plugins/input-mask/jquery.inputmask.extensions.js') }}"></script>
    <!-- date-range-picker -->
    <script src="{{ asset('assets/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <!-- bootstrap datepicker -->
    <script
        src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <!-- bootstrap color picker -->
    <script
        src="{{ asset('assets/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
    <!-- bootstrap time picker -->
    <script src="{{ asset('assets/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js"></script>
