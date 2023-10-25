<!doctype html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css?family=Courgette" rel="stylesheet">
    <style>
  @page { 
   size: auto; 
   margin: 0; 
} 

@font-face {
  font-family: texReg;
  src: url(https://brevfranissen.no/wp-content/themes/artistry/fonts/SnellBT-Regular.otf);
}
    </style>
</head>

<body>
    <?php 
        // Retrieve the values using $_GET
        $name = $_GET['name'];
        $address = $_GET['address'];
    ?>
    <div style="width:832px; height:378px; background:#ddd; text-align:center; margin:auto; display:flex; align-items:center; justify-content:center;">
        <div>
        <p style="margin:0px; font-family:Courgette; font-size:40px;line-height:10px;"><?php echo  $name ?></p><br />
        <p style="margin:0px; font-family:Courgette; font-size:40px;line-height:10px;"><?php echo  $address ?></p>
        </div>    
    </div>
    <!-- <button style="background: #000;  color: #fff; margin: auto;  padding: 10px 20px;  text-decoration: none;  margin-top: 15px !important; border:none; cursor:pointer;
    display: inherit;" onclick="window.print()">Print</button> -->

</body>
 
</html>