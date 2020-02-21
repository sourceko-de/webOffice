<?php 

$ip   = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$time = time();
$regSession = $ip.$time;

$_SESSION['regsession'] = $regSession;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="webOffice">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>webOffice - Account Registration</title>

    <!-- Font Icon -->
    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="main">

        <div class="container">
            <h2>Complete your registration</h2>
            <form method="POST" id="signup-form" class="signup-form">
                <h3>
                    <span class="title_text">Account Infomation</span>
                </h3>
                <fieldset>
                    <div class="fieldset-content">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" placeholder="User Name" />
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" placeholder="Your Email" />
                        </div>
                        <div class="form-group form-password">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" data-indicator="pwindicator" />
                            <div id="pwindicator">
                                <div class="bar-strength">
                                    <div class="bar-process">
                                        <div class="bar"></div>
                                    </div>
                                </div>
                                <div class="label"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cpassword" class="form-label">Confirm Password</label>
                            <input type="password" name="cpwd" id="cpwd" placeholder="confirm password" />
                        
                        </div>
                    </div>
                    <div class="fieldset-footer">
                        <span>Step 1 of 3</span>
                    </div>
                </fieldset>

                <h3>
                    <span class="title_text">Personal Information</span>
                </h3>
                <fieldset>

                    <div class="fieldset-content">
                        <div class="form-group">
                            <label for="full_name" class="form-label">Full name</label>
                            <input type="text" name="full_name" id="full_name" placeholder="Full Name" />
                        </div>
    
                        <div class="form-select">
                            <label for="country" class="form-label">Country</label>
                            <select name="country" id="country">
                                <option value="">Country</option>
                                <option value="Australia">Australia</option>
                                <option value="USA">America</option>
                                <option value="South Africa">South Africa</option>
                            </select>
                        </div>
    
                        <div class="form-radio">
                            <label for="gender" class="form-label">Gender</label>
                            <div class="form-radio-item">
                                <input type="radio" name="gender" value="male" id="male" checked="checked" />
                                <label for="male">Male</label>
    
                                <input type="radio" name="gender" value="female" id="female" />
                                <label for="female">Female</label>
                            </div>
                        </div>
    
                        <div class="form-group">
                            <label for="phone" class="form-label">Mobile no.</label>
                            <input type="text" name="mobile" id="mobile" placeholder="Your Cellphone" />
                        
                        </div>
                    </div>

                    <div class="fieldset-footer">
                        <span>Step 2 of 3</span>
                    </div>

                </fieldset>

                <h3>
                    <span class="title_text">Company Details</span>
                </h3>
                <fieldset>
                    <div class="fieldset-content">
                        
                        <div class="form-row">
                            <div class="form-group" style="width: 60%;">
                                <label for="company" class="form-label">Company Name</label>
                                <input type="text" name="company" id="company" />
                            </div>
                            <div class="form-group" style="width: 40%;">
                                <label for="reg" class="form-label">Reg no.</label>
                                <input type="text" name="reg" id="reg" />
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" placeholder="building no, street name..." id="address" />
                            </div>
                            
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="width: 60%;">
                                <label for="city" class="form-label">City/Town</label>
                                <input type="text" name="city" id="city" />
                            </div>
                            <div class="form-group" style="width: 40%;">
                                <label for="postal" class="form-label">Postal Code</label>
                                <input type="text" name="postal" id="postal" />
                                <input type="hidden" id="reg_session" value="<?php echo $_SESSION['regsession']; ?>" />
                            </div>
                        </div>
                        
                    </div>

                    <div class="fieldset-footer">
                        <span>Step 3 of 3</span>
                    </div>
                </fieldset>
            </form>
        </div>

    </div>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="vendor/jquery-validation/dist/additional-methods.min.js"></script>
    <script src="vendor/jquery-steps/jquery.steps.min.js"></script>
    <script src="vendor/minimalist-picker/dobpicker.js"></script>
    <script src="vendor/jquery.pwstrength/jquery.pwstrength.js"></script>
    <script src="js/main.js"></script>
</body>

</html>