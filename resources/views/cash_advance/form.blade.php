<div class="modal fade" id="modal-form" tabindex="1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form  id="form-item" method="post" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data" >
                {{ csrf_field() }} {{ method_field('POST') }}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title"></h3>
                </div>


                <div class="modal-body">
                    
                    <input type="hidden" id="id" name="id">
                    <div class="box-body">
                    <div class="form-group">
                    <label>Employee Number</label>
                    <select class="form-control" id="employee_id" name="employee_id">
                    <option disabled>--select employee number--</option>
                    @foreach($empo as $d)
                        <option value="{{$d->id}}">{{$d->employee_number}}</option>
                    @endforeach
                    </select>
                    </div>
                    <div class="form-group">
                            <label>Amount</label>
                            <input type="text" class="form-control" id="amount" name="amount" required>
                            <span class="help-block with-errors"></span>
                        </div>  
                    </div>
                    <div class="form-group">
                    <label>From Account</label>
                    <select class="form-control" id="account_id" name="account_id">
                    <option disabled>--select accountr--</option>
                    @foreach($account as $d)
                        <option value="{{$d->id}}">{{$d->account_name}}</option>
                    @endforeach
                    </select>
                    </div>
                    <!-- /.box-body -->

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->