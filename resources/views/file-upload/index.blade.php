<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Parser API Example</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5" style="max-width: 500px">       
        <div class="alert alert-warning mb-4 text-center">
           <h2 class="display-6">jQuery Ajax File uploading with Progress Bar Example - Laravelcode</h2>
        </div>  

        <form id="fileUploadForm" method="POST" action="{{ url('store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group mb-3">
                <input name="file" type="file" class="form-control">
            </div>

            <div class="d-grid mb-3">
                <input type="submit" value="Submit" class="btn btn-primary">
            </div>

            <div class="form-group">
                <div class="progress">
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
                    beforeSend: function () {
                        var percentage = '0';
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        var percentage = percentComplete;
                        $('.progress .progress-bar').css("width", percentage+'%', function() {
                          return $(this).attr("aria-valuenow", percentage) + "%";
                        })
                    },
                    complete: function (xhr) {
                        console.log('File has uploaded');
                    }
                });
            });
        });
    </script>
</body>
</html>