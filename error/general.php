<!DOCTYPE html>
<head>
    <title><?php echo $heading; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
        ::selection {background-color:#68B4FF;color: white;}
        ::moz-selection {background-color:#68B4FF;color: white;}
        ::webkit-selection {background-color: #68B4FF;color: white;}
        body {margin: 20px;font: 13px/20px normal Helvetica, Arial, sans-serif;color: #4F5155;}
        a {color: #0099FF;background-color: transparent;font-weight: normal;}
        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
        }
        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }
        #container {margin: 0 auto; border: 1px solid #D0D0D0; -webkit-box-shadow: 0 0 8px #D0D0D0;}
        p {margin: 12px 15px 12px 15px;}
    </style>
</head>
<body>
    <div id="container">
        <h1><?php echo $heading; ?></h1>
        <p><?php echo $message; ?><p> 
    </div>
    <div class="container" style="text-align:center;">
        <p class="text-muted">
            Powered by <a href="//unframed.cc/" title="unframed">Unframed</a> v VERSION.RELEASE
        </p>
    </div>
</body>
</html>
