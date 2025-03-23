<style>
#content {
    width:300px;
    margin:30px auto;
}

#thermometer {
    width:70px;
    height:300px;
    position: relative;
    background: #ddd;
    border:1px solid #aaa;
    -webkit-border-radius: 12px;
       -moz-border-radius: 12px;
        -ms-border-radius: 12px;
         -o-border-radius: 12px;
            border-radius: 12px;

    -webkit-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
       -moz-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
        -ms-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
         -o-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
            box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
}

#thermometer .track {
    height:280px;
    top:10px;
    width:20px;
    border: 1px solid #aaa;
    position: relative;
    margin:0 auto;
    background: rgb(255,255,255);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgb(0,0,0)), color-stop(1%,rgb(255,255,255)));
    background: -webkit-linear-gradient(top, rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background:      -o-linear-gradient(top, rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background:     -ms-linear-gradient(top, rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background:    -moz-linear-gradient(top, rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background:   linear-gradient(to bottom, rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background-position: 0 -1px;
    background-size: 100% 5%;
}

#thermometer .progress {
    height:0%;
    width:100%;
    background: rgb(20,100,20);
    background: rgba(20,100,20,0.6);
    position: absolute;
    bottom:0;
    left:0;
}

#thermometer .goal {
    position:absolute;
    top:0;
}

#thermometer .stop1 {
    position:absolute;
    top:100%;
}

#thermometer .stop2 {
    position:absolute;
    top:75%;
}

#thermometer .stop3 {
    position:absolute;
    top:50%;
}

#thermometer .stop4 {
    position:absolute;
    top:25%;
}

#thermometer .amount {
    display: inline-block;
    padding:0 5px 0 60px;
    border-top:1px solid white;
    font-family: Trebuchet MS;
    font-weight: bold;
    color:#fff;
}

#thermometer .progress .amount {
    padding:0 60px 0 5px;
    position: absolute;
    border-top:1px solid #060;
    color:#060;
    right:0;
}

</style>

<div id="content">

    <div id="thermometer">

        <div class="track">
            <div class="goal">
                <div class="amount"> 91,100 </div>
            </div>
            <div class="progress">
                <div class="amount">0 </div>
            </div>
            <div class="stop1">
                <div class="amount">$0 </div>
            </div>
            <div class="stop2">
                <div class="amount">$22775 </div>
            </div>
            <div class="stop3">
                <div class="amount">$45550 </div>
            </div>
            <div class="stop4">
                <div class="amount">$68325 </div>
            </div>
        </div>

    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function formatCurrency(n, c, d, t) {
    "use strict";

    var s, i, j;

    c = isNaN(c = Math.abs(c)) ? 2 : c;
    d = d === undefined ? "." : d;
    t = t === undefined ? "," : t;

    s = n < 0 ? "-" : "";
    i = parseInt(n = Math.abs(+n || 0).toFixed(c), 10) + "";
    j = (j = i.length) > 3 ? j % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

/**
 * Thermometer Progress meter.
 * This function will update the progress element in the "thermometer"
 * to the updated percentage.
 * If no parameters are passed in it will read them from the DOM
 *
 * @param {Number} goalAmount The Goal amount, this represents the 100% mark
 * @param {Number} progressAmount The progress amount is the current amount
 * @param {Boolean} animate Whether to animate the height or not
 *
 */
function thermometer(goalAmount, progressAmount, animate) {
    "use strict";

    var $thermo = $("#thermometer"),
        $progress = $(".progress", $thermo),
        $goal = $(".goal", $thermo),
        percentageAmount;

    goalAmount = goalAmount || parseFloat( $goal.text() ),
    progressAmount = progressAmount || parseFloat( $progress.text() ),
    percentageAmount =  Math.min( Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point

    //let's format the numbers and put them back in the DOM
    //show currency with decimal places
    //$goal.find(".amount").text( "$" + formatCurrency( goalAmount ) );
    //$progress.find(".amount").text( "$" + formatCurrency( progressAmount ) );
    //show currency without decimnal places
    $goal.find(".amount").text( "$" + ( goalAmount ) );
    $progress.find(".amount").text( "$" + ( progressAmount ) );
    //show not as curency
    //$goal.find(".amount").text( goalAmount  );
    //$progress.find(".amount").text( progressAmount  );


    //let's set the progress indicator
    $progress.find(".amount").hide();
    if (animate !== false) {
        $progress.animate({
            "height": percentageAmount + "%"
        }, 1200, function(){
            $(this).find(".amount").fadeIn(500);
        });
    }
    else {
        $progress.css({
            "height": percentageAmount + "%"
        });
        $progress.find(".amount").fadeIn(500);
    }
}

current = 0;
function load(){
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      if(current != this.responseText){
        current = this.responseText;
        thermometer(91100,this.responseText,false);
      }
    }
  };
  xhttp.open("GET", "total_ajax.php", true);
  xhttp.send();
}
load();
setInterval(load,30000);

</script>
