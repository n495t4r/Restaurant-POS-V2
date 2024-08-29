<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <!-- Include necessary CSS for styling -->
    <!-- <link rel="stylesheet" href="{{ asset('path/to/your/css/styles.css') }}"> -->
</head>
<body>
    
  <div id="invoice-POS">
    <center id="top">
      <img src="{{ asset('images/poslogo.png') }}" alt="Logo" class="logo">
      <!-- <div class="info"> 
        <h2>PEVA Limited</h2>
      </div>End Info -->
    </center><!--End InvoiceTop-->
    
    <div id="mid">
      <div class="info">
        <center>PEVA Restaurant</center>
        <p> 
            Phase 4 Kubwa</br>
            pevafoodndrink@gmail.com</br>
            +234 903 393 2295</br>
            Date: {{ $created_at }}
        </p>
      </div>
      
    </div><!--End Invoice Mid-->
    
    <div id="bot">
    <p class="itemtext">{{$invoiceNo}}</p>
    <p class="itemtext">Paid: {{number_format($paid,2)}}</p>
    
        <div id="table">
            <table>
                <tr class="tabletitle">
                    <td class="item"><h2>Item</h2></td>
                    <td class="Hours"><h2></h2></td>
                    <td class="Rate"><h2>Sub Total</h2></td>
                </tr>

                <!-- Loop through the cart items and output table rows dynamically -->
                @foreach ($cartData as $item)
                    <tr class="service">
                        <td class="tableitem"><p class="itemtext">{{ $item['quantity'] }}x {{ $item['name'] }}</p></td>
                        <td class="tableitem"><p class="itemtext"></p></td>
                        <td class="tableitem"><p class="itemtext">{{ config('settings.currency_symbol') }}{{ number_format($item['price'],2) }}</p></td>
                    </tr>
                @endforeach
                               
                <tr class="tabletitle">
                    <td class="Rate"><h2>Invoice Total</h2></td>
                    <td colspan="2" class="payment"><h2>{{ config('settings.currency_symbol') }}{{ number_format($price,2) }}</h2></td>
                </tr>

               
            </table>
        </div><!--End Table-->

        <div id="legalcopy">
            <p class="legal"><i>Thank you for your patronage!</i>
            </p>
        </div>

    </div><!--End InvoiceBot-->
  </div><!--End Invoice-->



</body>
<script>

(function() {
  window.print()
})();

</script>
<style>
    #invoice-POS{
    box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
    padding:2mm;
    margin: 0 auto;
    width: 44mm;
    background: #FFF;
    }
    
    ::selection {background: #f31544; color: #FFF;}
    ::moz-selection {background: #f31544; color: #FFF;}
    h1{
    font-size: 1.5em;
    /* color: #222; */
    }
    h2{font-size: .7em;}
    h3{
    font-size: 1.2em;
    font-weight: 300;
    line-height: 2em;
    }
    p{
    font-size: .7em;
    /* color: #666; */
    line-height: 1.2em;
    }
    
    #top, #mid,#bot{ /* Targets all id with 'col-' */
    border-bottom: 1px solid #EEE;
    }

    #top{min-height: 50px;}
    #mid{min-height: 80px;} 
    #bot{ min-height: 50px;}

    #top .logo{
    /* //float: left; */
        height: 50px;
        width: 60px;
        /* background: url( "{{asset('images/poslogo.png') }}" ) no-repeat; */
        background-size: 60px 40px;
    }
    .clientlogo{
        float: left;
        height: 60px;
        width: 60px;
        background: url("{{asset('images/pevalogo.png') }}" ) no-repeat;
        background-size: 60px 60px;
        border-radius: 50px;
    }
    .info{
        display: block;
    /* //float:left; */
        margin-left: 0;
    }
    .title{
        float: right;
    }
    .title p{text-align: right;} 
    table{
        width: 100%;
        border-collapse: collapse;
    }
    td{
    /* //padding: 5px 0 5px 15px; */
    /* //border: 1px solid #EEE */
    }
    .tabletitle{
    /* //padding: 5px; */
        font-size: .9em;
        background: #EEE;
    }
    .service{border-bottom: 1px solid #EEE;}
    .item{width: 24mm;}
    .itemtext{font-size: .7em;}

    #legalcopy{
    margin-top: 5mm;
    }
</style>
</html>




