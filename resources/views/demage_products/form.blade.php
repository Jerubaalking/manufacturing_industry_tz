<div class="modal fade" id="modal-demage" tabindex="1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form  id="form-demage" method="post" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data" >
                {{ csrf_field() }} {{ method_field('POST') }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title"></h3>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="sales_id1" name="sales_id" class="form-control">
                    <div class="box-body">
                    <div class="row">
                      <div class='col-md-12'>
                      <label>Date</label>
                      <input type="date" class="form-control" id="date_in" name="date_in"  >
                     <span class="help-block with-errors"></span>
                      </div>
                      <div class='col-md-12'>
                      <label>Demage Qty</label>
                      <input type="number" class="form-control" id="demage_qty" name="qty" min="1">
                     <span class="help-block with-errors"></span>
                      </div>
                      </div>
                      <hr>
                      </hr>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                
                    <button type="submit" class="btn btn-primary subBtn">Save</button>
                </div>

            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


    


    


