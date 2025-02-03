<title>Live Telethon Updates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
.thermometer {
    float: left;
    margin: 0 150px
}

.thermometer {
    width: 70px;
    height: 300px;
    position: relative;
    background: #ddd;
    border: 1px solid #aaa;
    -webkit-border-radius: 12px;
    -moz-border-radius: 12px;
    -ms-border-radius: 12px;
    -o-border-radius: 12px;
    border-radius: 12px;
    -webkit-box-shadow: 1px 1px 4px #999,5px 0 20px #999;
    -moz-box-shadow: 1px 1px 4px #999,5px 0 20px #999;
    -ms-box-shadow: 1px 1px 4px #999,5px 0 20px #999;
    -o-box-shadow: 1px 1px 4px #999,5px 0 20px #999;
    box-shadow: 1px 1px 4px #999,5px 0 20px #999
}

.thermometer .track {
    height: 280px;
    top: 10px;
    width: 20px;
    border: 1px solid #aaa;
    position: relative;
    margin: 0 auto;
    background: #fff;
    background: -webkit-gradient(linear,left top,left bottom,color-stop(0%,rgb(0,0,0)),color-stop(1%,rgb(255,255,255)));
    background: -webkit-linear-gradient(top,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -o-linear-gradient(top,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -ms-linear-gradient(top,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -moz-linear-gradient(top,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: linear-gradient(to bottom,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background-position: 0 -1px;
    background-size: 100% 5%
}

.thermometer .progress {
    height: 0%;
    width: 100%;
    background: #146414;
    background: rgba(20,100,20,.6);
    position: absolute;
    bottom: 0;
    left: 0
}

.thermometer .goal {
    position: absolute;
    top: 0
}

.thermometer .amount {
    display: inline-block;
    padding: 0 5px 0 60px;
    border-top: 1px solid #000;
    font-family: Trebuchet MS;
    font-weight: 700;
    color: #333
}

.thermometer .progress .amount {
    padding: 0 60px 0 5px;
    position: absolute;
    border-top: 1px solid #060;
    color: #060;
    right: 0
}

.thermometer.horizontal {
    margin: 30px auto
}

.thermometer.horizontal {
    width: 100%;
    height: 70px
}

.thermometer.horizontal .track {
    width: 90%;
    left: 0;
    height: 20px;
    margin: 14px auto;
    background: -webkit-gradient(linear,left top,right top,color-stop(0%,rgb(0,0,0)),color-stop(1%,rgb(255,255,255)));
    background: -webkit-linear-gradient(left,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -o-linear-gradient(left,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -ms-linear-gradient(left,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: -moz-linear-gradient(left,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background: linear-gradient(to right,rgb(0,0,0) 0%,rgb(255,255,255) 10%);
    background-size: 5% 100%
}

.thermometer.horizontal .progress {
    height: 100%;
    width: 0%
}

.thermometer.horizontal .goal {
    left: 100%;
    height: 100%
}

.thermometer.horizontal .amount {
    bottom: 0;
    position: absolute;
    padding: 0 5px 50px;
    border-top: 0;
    border-left: 1px solid #000
}

.thermometer.horizontal .progress .amount {
    border-left: 0;
    border-top: 0;
    border-right: 1px solid #060
}
</style>
<div style="padding:10px">
<div class="row">
    <div class="col-3">
        <h2 class="text-center" id="time">Live Orders</h2>
    </div>
    <div class="col-6">
        <h1 class="text-center">WRUW Telethon 2024</h1>
    </div>
    <div class="col-3">
        <h2 class="text-center" id="date">Live Orders</h2>
    </div>
</div>
<div class="row">
    <div class="col-3">
        <h2 class="text-center">Total Statistics</h2>
        <div class="row">
            <div class="col-6">
                <p>Total: $<span id="total">0</span></p>
            </div>
            <div class="col-6">
                <p>Percent of telethon: <span id="percentdone">0</span>%</p>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <p>Percent towards goal: <span id="percentgoal">0</span>%</p>
            </div>
            <div class="col-6">
                <p>Percent of goal relative to time: <span id="percentrelative">0</span>%</p>
            </div>
        </div>
        <div class="thermometer horizontal" id="totalthermometer">
            <div class="track">
                <div class="goal">
                </div>
                <div class="progress">
                    <div class="amount">0 </div>
            </div>
        </div>
        </div>
        <h2 class="text-center">Show Statistics</h2>
        <div class="row">
            <div class="col-6">
                <p>Current Show: <span id="showname"></span></p>
            </div>
            <div class="col-6">
                <p>Goal: $<span id="showgoal">0</span></p>
            </div>
        </div>
        <div class="row">
        <div class="col-6">
                <p>Total: $<span id="showtotal">0</span></p>
            </div>
            <div class="col-6">
                <p>Percent towards goal: <span id="showpercent">0</span>%</p>
            </div>
        </div>
        <div class="thermometer horizontal" id="showthermometer">
             <div class="track">
                <div class="goal">
                </div>
                <div class="progress">
                    <div class="amount">0 </div>
            </div>
        </div>
        </div>
    </div>
    <div class="col-9">
        <h2 class="text-center">Amount Raised in the past</h2>
        <div class="row">
            <div class="col-3">
            </div>
            <div class="col-2">
                <p style="text-align:center;">1 hour: $<span id="hour">0</span></p>
            </div>
            <div class="col-2">
                <p style="text-align:center;">2 hours: $<span id="two">0</span></p>
            </div>
            <div class="col-2">
                <p style="text-align:center;">24 hours: $<span id="twentyfour">0</span></p>
            </div>
            <div class="col-3">
            </div>
        </div>
        <h2 class="text-center">Most Recent Donations</h2>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">
                        Time
                    </th>
                    <th scope="col">
                        Name
                    </th>
                    <th scope="col">
                        Location
                    </th>
                    <th scope="col">
                        Order Amount
                    </th>
                    <th scope="col">
                        Items Donated
                    </th>
                    <th scope="col">
                        Show(s) donated to
                    </th>
                </tr>
            </thead>
            <tbody id="data">

            </tbody>
        </table>
    </div>
</div>
</div>

<script>

    function ce(tag, options, html){
        e = document.createElement(tag);
        for(o in options){
            e.setAttribute(o,options[o]);
        }
        if(html){
            e.innerHTML = html;
        }
        return e;
    }
    var olddata = null;
    var shows = {};
    $.getJSON('https://api.wruw.org/schedule', function(data){
        for(i in data){
            shows[strtoslug(data[i]['title'])] = data[i]['title'];
        }
        update(true);
        });
    //initial
    function update(init){
        $.getJSON('/live-ajax.php', function(data){
            table = document.getElementById('data');
            table.innerHTML = '';
            for(i in data['orders']){
                neworder = true;
                order = data['orders'][i];
                for(old in olddata){
                    if(olddata[old]['post_id'] == order['post_id']){
                        neworder = false;
                        break;
                    }
                }
                if(neworder && !init){
                    if(order['onair'] == 'yes'){
                        notifyMe('New order from '+order['name']+' in '+order['city']+', '+order['state']+' for $'+order['amount'],order['items'].join(', '));

                    }else{
                        notifyMe('New anonymous order from '+order['city']+', '+order['state']+' for $'+order['amount'],order['items'].join(', '));
                    }
                }
                row = ce('tr');
                time = new Date(order['time']);
                row.appendChild(ce('td',{},time.toLocaleDateString()+' - '+time.toLocaleTimeString()));
                table.appendChild(row);
                if(order['onair'] == 'yes'){
                    row.appendChild(ce('td',{},order['name']));
                }else{
                    row.appendChild(ce('td',{},'Anonymous'));
                }
                row.appendChild(ce('td',{},order['city']+', '+order['state']));
                row.appendChild(ce('td',{},'$'+order['amount']));
                row.appendChild(ce('td',{},order['items'].join(', ')));
                if(order['showname']){
                    var shownames = order['showname'].split(',');
                    for(i in shownames){
                        shownames[i] = shows[shownames[i]];
                    }
                    row.appendChild(ce('td',{},shownames.join(', ')));
                }else{
                    row.appendChild(ce('td',{},''));
                }
            }
            olddata = data['orders'];
            document.getElementById('total').innerHTML=money(data['total']);
            document.getElementById('percentgoal').innerHTML=Math.round(data['total']/91000*100);
            document.getElementById('hour').innerHTML=money(data['hour']);
            document.getElementById('two').innerHTML=money(data['two']);
            document.getElementById('twentyfour').innerHTML=money(data['twentyfour']);
            thermometer('totalthermometer', 91000, data['total'], false);
        });
    }
    setInterval(function(){
        update(false);
    },30000);   
    function updateshow(){
        showslug = strtoslug(document.getElementById('showname').innerHTML);
        $.getJSON('/show_ajax.php?showname='+showslug, function(data){
            document.getElementById('showgoal').innerHTML=money(data['goal']);
            document.getElementById('showtotal').innerHTML=money(data['total']);
            document.getElementById('showpercent').innerHTML=Math.round(data['total']/data['goal']*100);
            thermometer('showthermometer', parseFloat(data['goal']), data['total'], false);
        });
    }
    setInterval(function(){
        getcurrentshow();
    },60000);

    function notifyMe(title, description) {
      if (Notification.permission === "granted") {
        // If it's okay let's create a notification
        var notification = new Notification(title,{body:description,silent:true});
      }

      // Otherwise, we need to ask the user for permission
      else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
          // If the user accepts, let's create a notification
          if (permission === "granted") {
              var notification = new Notification(title,{body:description,silent:true});
          }
        });
      }
    }

    function money(amount){
        if(!amount){
            amount = '0';
        }
        amount = amount.toString();
        parts = amount.split('.');
        dollars = parts[0];
        if (parts.length>1){
            cents = parts[1].toString();
            if(cents.length<2){
                cents += '0';
            }else{
                cents = cents.substring(0,2);
            }
        }else{
            cents = '00'
        }
        dollarstring = '';
        counter = 0;
        for(i=0; i< dollars.length; i++){
            if(counter == 3){
                dollarstring = ',' + dollarstring;
                counter = 0;
            }
            dollarstring = dollars[dollars.length-i-1] + dollarstring;
            counter+=1;
        }
        return dollarstring+'.'+cents;
    }
    function updatetime(){
        var d = new Date();
        var n = d.toLocaleTimeString();
        document.getElementById('time').innerHTML = n;
        var y = d.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' });
        document.getElementById('date').innerHTML = y;
        start = new Date('2024-03-18 00:00:00').getTime();
        end = new Date('2024-03-25 00:00:00').getTime();
        percent = (d.getTime() - start) / (end - start);
        document.getElementById('percentdone').innerHTML = Math.round(percent*100);
        amount = parseFloat(document.getElementById('total').innerHTML.replace(',', ''));
        relative = Math.round((amount /(91100*percent)) * 100);
        document.getElementById('percentrelative').innerHTML = relative;

    }
    setInterval(function(){
        updatetime();
    },1000);

    function getcurrentshow(){
        $.getJSON('https://api.wruw.org/currentshow',function(data){
            document.getElementById('showname').innerHTML = data['title'];
            updateshow();
        });
    }
    $.getJSON('https://api.wruw.org/currentshow',function(data){
                document.getElementById('showname').innerHTML = data['title'];
            updateshow();    
            });

    function thermometer(id, goalAmount, progressAmount, animate) {
    "use strict";

    var $thermo = $("#"+id),
        $progress = $(".progress", $thermo),
        $goal = $(".goal", $thermo),
        percentageAmount,
        isHorizontal = $thermo.hasClass("horizontal"),
        newCSS = {};

    goalAmount = goalAmount || parseFloat( $goal.text() ),
    progressAmount = progressAmount || parseFloat( $progress.text() ),
    percentageAmount =  Math.min( Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point

    //let's format the numbers and put them back in the DOM
    $goal.find(".amount").text( "$" + money( goalAmount ) );
    $progress.find(".amount").text( "$" + money( progressAmount ) );


    //let's set the progress indicator
    $progress.find(".amount").hide();

    newCSS[ isHorizontal ? "width" : "height" ] = percentageAmount + "%";

    if (animate !== false) {
        $progress.animate( newCSS, 1200, function(){
            $(this).find(".amount").fadeIn(500);
        });
    }
    else {
        $progress.css( newCSS );
        $progress.find(".amount").fadeIn(500);
    }
}
function strtoslug(str)
{
    if(str){
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
        var to   = "aaaaeeeeiiiioooouuuunc------";

        for (var i=0, l=from.length ; i<l ; i++)
        {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes
    }
	return str;
}

</script>
