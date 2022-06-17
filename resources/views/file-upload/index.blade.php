<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Parser API Example</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>

    <div class="container mt-5" style="max-width: 500px">       
        <div class="alert alert-warning mb-4 text-center">
           <h2 class="display-6">Document Parser API Example with api.pdf.co</h2>
        </div>  

        <form id="fileUploadForm" method="POST" action="{{ url('store') }}" enctype="multipart/form-data">
            @csrf
            @if ($message = Session::get('status'))
            <div class="alert alert-success">
                <strong>{!! $message !!}</strong>
            </div>
            @endif
            <div class="sign__input-wrapper mb-25">
                <span id="alert-parser" class="alert alert-danger form-control" style="width: 100%;display : none;"></span>
                <span id="success-parser" class="alert alert-success form-control" style="width: 100%;display : none;"></span>
            </div>
            <div class="form-group mb-3">
                <label>Input File (*.pdf, *.jpg, *.png, *.tif, *.gif, *.bmp)</label>
                <input name="fileInput" type="file" class="form-control" style="margin-top: 10px;">
            </div>

            <div class="form-group mb-3">
                <label>Input Template (*.yml)</label>
                <input name="fileTemplate" type="file" class="form-control" style="margin-top: 10px;">
            </div>

            <div class="d-grid mb-3">
                <input type="submit" value="Submit" class="btn btn-primary">
            </div>

            <div class="form-group">
                <div class="progress" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                </div>
            </div>
        </form>
    </div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
<script>
        $(function () {
            $(document).ready(function () {
                $('#fileUploadForm').ajaxForm({
                    //dataType : 'json', 
                    beforeSend: function () {
                        var percentage = '0';
                        $('#success-parser').show();
                        $('#success-parser').html('<i class="fa fa-circle-o-notch fa-spin"></i> Loading');
                    },
                    /*uploadProgress: function (event, position, total, percentComplete) {
                        $('.progress').show();
                        var percentage = percentComplete;
                        $('.progress .progress-bar').css("width", percentage+'%', function() {
                          return $(this).attr("aria-valuenow", percentage) + "%";
                        });
                        
                    },*/
                    complete: function (xhr) {
                        //console.log('File has uploaded');
                        console.log(xhr);
                        
                        if(xhr.status !== 200) {
                            $('#success-parser').hide();
                            $('#alert-parser').show();
                            $('#alert-parser').html(xhr.message);                        
                        }else{
                            location.reload();
                            $('#alert-parser').hide();
                            //$('#success-parser').html('<a href="'+xhr.message+'">'+xhr.message+'</a>');                     
                        }
                    },
                    error: function(response) {
                        //console.log(response);
                        $('#success-parser').hide();
                        $('#alert-parser').show();
                        $('#alert-parser').html(response.message); 
                        //alert(response.responseJSON.message);
                    }
                });
            });
        });
    </script>
</body>
</html>