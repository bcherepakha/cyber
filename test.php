<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
    <meta name="language" content="ru">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

    <style>
        body { padding-left: 100px; }
        #actionTest { width: 700px; float: left; }
        #actionTest dl { width: 600px; overflow: hidden; }
        #actionTest dt { width: 60px; margin-right: 10px; float: left; display: block; clear: left; margin-top: 10px; }
        #actionTest dd { overflow: hidden; display: block; margin-top: 10px; }
        #actionTest dt, dd { height: 40px; line-height: 40px; }
        #actionTest p { text-align: center; width: 600px; }
        #actionTest dl input, #actionTest dl select { width: 300px; }

        #response { width: 600px; overflow: hidden; }
    </style>
</head>
<body>
    <h1>Тестирование MTE CyberPlat</h1>
    <form id="actionTest">
        <dl>
            <dt><label for="action">Action</label></dt>
            <dd><select id="action">
                    <option value="check">Check</option>
                    <option value="payment">Payment</option>
                    <option value="cancel">Cancel</option>
                    <option value="status">Status</option>
                    <option value="left">left</option>
                </select>
                <button id="generateAction">Случайно</button>
            </dd>

            <dt><label for="number">Number</label></dt>
            <dd>
                <input type="text" id="number" val="">
                <button id="generateNumber">Случайно</button>
            </dd>

            <dt><label for="type">Type</label></dt>
            <dd>
                <input type="text" id="type" val="">
                <button id="generateType">Случайно</button>
            </dd>

            <dt><label for="amount">Amount</label></dt>
            <dd>
                <input type="text" id="amount" val="">
                <button id="generateAmount">Случайно</button>
            </dd>

            <dt><label for="receipt">Receipt</label></dt>
            <dd>
                <input type="text" id="receipt" val="">
                <button id="generateReceipt">Случайно</button>
            </dd>

            <dt><label for="date">Date</label></dt>
            <dd>
                <input type="text" id="date" val="">
                <button id="generateDate">Случайно</button>
            </dd>

            <dt><label for="mes">Mes</label></dt>
            <dd>
                <input type="text" id="mes" val="">
                <button id="generateMes">Случайно</button>
            </dd>

            <dt><label for="sign">Sign</label></dt>
            <dd>
                <input type="text" id="sign" val="">
                <button id="generateSign">Подписать</button>
            </dd>
        </dl>

        <p><input type="submit" value="Отправить"></p>
        <p><button id="refresh" type="button">Очистить</button></p>
    </form>

    <div id="response">
    </div>

<script>
    $( document).ready(function(){
        console.log('Start');

        $('#generateNumber').on('click', function( event ){
            event.preventDefault();
            $('#number').val( getRandomInt(0, 1234567890) + '' +  getRandomInt(0, 1234567890) + '' + getRandomInt(0, 1234567890) );
        });

        $('#generateType').on('click', function( event ){
            event.preventDefault();
            $('#type').val( getRandomInt(1, 12) );
        });

        $('#generateAmount').on('click', function( event ){
            event.preventDefault();
            $('#amount').val( getRandomInt(1, 1234567) + '.' + getRandomInt(0, 10) );
        });

        $('#generateReceipt').on('click', function( event ){
            event.preventDefault();
            $('#receipt').val( getRandomInt(1, 123456789012345) );
        });

        $('#generateDate').on('click', function( event ){
            event.preventDefault();
            //YYYY-MM-DDThh:mm:ss
            var mm = getRandomInt(1, 12);
            if (mm<10) mm = '0' + mm;
            var dd = getRandomInt(1, 40);
            if (dd<10) dd = '0' + dd;
            var hh = getRandomInt(0, 24);
            if (hh<10) hh = '0' + hh;
            var ii = getRandomInt(0, 60);
            if (ii<10) ii = '0' + ii;
            var ss = getRandomInt(0, 60);
            if (ss<10) ss = '0' + ss;
            $('#date').val( getRandomInt(2010, 2020) + '-' + mm + '-' + dd + 'T' + hh + ':' + ii + ':' + ss);
        });

        $('#generateMes').on('click', function( event ){
            event.preventDefault();
            $('#mes').val( getRandomInt(1, 999) );
        });

        $('#generateSign').on('click', function( event ){
            event.preventDefault();
            $('#sign').val('');
            var param = getParam();
            $.ajax( 'getSign.php' + param )
                .done(function( data ){
                    $('#sign').val( data );
                });
        });

        $('#actionTest').on('submit', function( event ){
            event.preventDefault();

            var param = getParam();
            var url = 'index.php' + param;
            $.ajax( url, { dataType : 'text' } )
                .done(function( data ){
                    console.log( data );
                    $('#response').append( $('<p></p>').text( url ) ).append( $('<code></code>').text( data ) );
                });

            return false;
        });

        $('#refresh').on('click', function(){
            $('#response').text('');
        });
    });

    //Генерирует строку параметров
    function getParam() {
        var param = [];
        var check = ['action', 'number', 'type', 'amount', 'receipt', 'date', 'mes', 'sign'];
        for (i=0; i<check.length; i++) {
            p = check[i];
            if ( ''!=$('#' + p).val() ) param.push(p + '=' + $('#' + p).val());
        }

        var str = '?' + param[0];
        for (i=1; i< param.length; i++) {
            str += '&' + param[i];
        }

        return str;
    }

    // Возвращает произвольный integer между min (включая) и max (не включая)
    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min)) + min;
    };


</script>
</body>
</html>
