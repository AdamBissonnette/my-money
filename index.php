<?php
function Getfloat($str) { 
  if(strstr($str, ",")) { 
    $str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs 
    $str = str_replace(",", ".", $str); // replace ',' with '.' 
  } 
  
  if(preg_match("#([0-9\.]+)#", $str, $match)) { // search for number that may contain '.' 
    return floatval($match[0]); 
  } else { 
    return floatval($str); // take some last chances with floatval 
  } 
}

function AddPurchase($amount)
{
    $sql = sprintf("INSERT INTO Purchase (amount, date) VALUES (%s, '%s')", $amount, date("Y-m-d H:i:s"));
    $db = new SQLite3('money.db');

    $res = $db->query($sql);
}

function GenReport()
{
    $template = '<tr id="row-%s"><td>$%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
    $controlTemplate = '<button type="button" class="btn btn-default" aria-label="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
    $controlTemplate .= '<button type="button" class="btn btn-default" aria-label="Trash"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
    $db = new SQLite3('money.db');

    $res = $db->query('SELECT * FROM Purchase ORDER BY id DESC');
    $output = '<div class="panel panel-default"><div class="panel-heading">Purchase Report</div>';
    $output .= '<table class="table table-striped"><tr id="row-header"><th>Amount</th><th>Date and Time</th><th>Note</th><th>Controls</th></tr>';
    while ($row = $res->fetchArray()) {
        $output .= sprintf($template, $row["id"], Getfloat($row["amount"]), $row["date"], "Made by Adam", $controlTemplate);
    }
    echo $output . "</table></div>";
}

?>

<html>
<head>
    <title>My Money</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style type="text/css">
        .smallish-area {margin: 20px auto 0;}
        #report div {text-align: center;}
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
        <div class="col-lg-12"><div class="smallish-area">
        <?php
        if (isset($_REQUEST["amount"])) {
            $purchase = Getfloat(floatval($_REQUEST["amount"]));

            $alertTemplate = '<div class="alert alert-%s" role="alert"><strong>%s</strong> %s</div>';
            $type = "danger";
            $brief = "Oh Snap!";
            $message = "We can't add purchases of 0 dollars!";


            if ($purchase != 0)
            {
                AddPurchase($purchase);
                echo sprintf($alertTemplate, "success", "Hooray!!", "You added a purchase of $" . $purchase . "!!");
            }
            else
            {
                echo sprintf($alertTemplate, $type, $brief, $message);
            }
        }
        ?>

        <form id="purchase" method="post">
            <div class="input-group input-group-lg">
              <span class="input-group-addon" id="sizing-addon1">$</span>
              <input name="amount" type="text" class="form-control" placeholder="Amount Spent" aria-describedby="sizing-addon1">
              <span class="input-group-btn">
                <input type="submit" class="btn btn-success" value="Add" />
              </span>
            </div>
        </form>

        <?php

        if (isset($_REQUEST["report"]))
        {
            echo GenReport();
        }
        ?>

        <form id="report" method="post">
            <div class="input-group input-group-lg">
                <input type="hidden" name="report" />
                <input type="submit" class="btn btn-lg btn-large btn-primary" value="Show Purchases" />
            </div>
        </form>

        </div>
        </div>
        </div>
    </div>
</body>
</html>