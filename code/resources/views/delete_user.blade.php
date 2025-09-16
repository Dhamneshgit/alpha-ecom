<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <section class="delete-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="delete-item">
                        <img src="https://grocypay.com/frontend/assets1/img/richpay1.png" alt="">
                        
                        <form action="{{ route('deleteuser') }}" method="POST">
                            @csrf <!-- CSRF token for security -->
                           
                            <div class="mb-3">
                                <div class="form-group">
                                    <label for="mobile" class="form-label">Delete Your Account</label>
                                    <input type="number" name="mobile" class="form-control" placeholder="Enter Your mobile" required>
                                </div>
                                <input type="submit" name="submit" value="submit" class="btn btn-primary" onclick="return confirmDelete()">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this user?");
        }
    </script>
    <style>
        .delete-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            gap: 20px;
        }

        .delete-item img {
            width: 100%;
            max-width: 300px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
        }

        .delete-item h3 {
            font-size: 25px;
            font-weight: 700;
            color: #1b4b9d;
        }
        .delete-item form {
            width: 40%;
            display: flex;
            align-items: center;
            flex-direction: column;
        }

        .delete-item form .form-group {
            margin-bottom: 20px;
        }

        .delete-item form div {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .delete-item .btn.btn-primary {
            width: 100%;
            max-width: 130px;
            font-size: 16px;
            text-transform: capitalize;
            font-weight: 700;
            background: #f73033;
            border: none;
        }

        .form-group .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #f73033;
        }
        .delete-section{
            background: url('https://grocypay.com/frontend/assets1/img/footer-bg.png');
        }
    </style>
</body>
</html>