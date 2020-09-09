<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>ArcaniaFr SRP PHP Test</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <style type="text/css">
        #output div {
            padding: 20px 0;
            overflow: auto;
        }
    </style>
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
      
      <div class="jumbotron">
          <div class="container">
                <h1>ArcaniaFr SRP PHP Client</h1>
                <p>Javascript Client for Login over Secure Remote Password Protocol</p>
          </div>
        
        </div>
      
      <div class="container">
          <form class="form-inline">
            <input type="text" id="username" placeholder="Username" class="form-control" />
            <input type="password" id="password"placeholder="Password" class="form-control" />
            
            <button id="btRegistrate" class="btn btn-default">registrate</button>
            <button id="btLogin" class="btn btn-default">login</button>
        </form>
          
          <div id="output"></div>
      </div>
      
         <a href="https://github.com/arcaniaFr/srpPHP" target="_blank" class="github-corner" aria-label="View source on Github">ArcaniaFr</a>
 
        
        
        <script type="text/javascript" src="js/bigint.js"></script>
        <script type="text/javascript" src="js/sha256.js"></script>
        <script type="text/javascript" src="js/srp.js"></script>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        
        <script type="text/javascript">
            
                var demo = {
                    srp: new srp(),
                    
                    registrate: function(username, password){
                        $("#output").html("");
                        var s = this.srp.getRandomSeed();
                        var x = this.srp.generateX(s, username, password);
                        var v = this.srp.generateV(x);
                        
                        var data = {phase: 0, I: username, s: s, v: v};
                        $("#output").append($("<div />").html("SEND: " + JSON.stringify(data)));
                        $.ajax({
                            url: "server.php",
                            method: 'GET',
                            dataType: 'json',
                            data: data,
                            success: function(res){
                                $("#output").append($("<div />").html("Receive " + JSON.stringify(res)));
                            }

                        })
                    },
                    
                    loginPhase1: function(username, password){
                        $("#output").html("");
                        var me = this;
                        
                        var a = this.srp.getRandomSeed();
                        var A = this.srp.generateA(a);
                        
                        var data = {phase: 1, I: username, A: A};
                        $("#output").append($("<div />").html("SEND: " + JSON.stringify(data)));
                        $.ajax({
                            url: "server.php",
                            method: 'GET',
                            dataType: 'json',
                            data: data,
                            success: function(res){
                                $("#output").append($("<div />").html("Receive " + JSON.stringify(res)));
                                
                                if(!res.success){
                                    $("#output").append($("<div />").html("USER NOT FOUND!"));
                                    return;
                                }
                                
                                var s = res.s;
                                var x = me.srp.generateX(s, username, password);
                                
                                demo.loginPhase2(a,A, res.B, x);
                            }
                        });
                    },
                    
                    loginPhase2: function(a, A, B, x){
                        var me = this;
                        
                        var S = me.srp.generateS_Client(A, B, a, x);
                        var M1  = me.srp.generateM1(A, B, S);
                        
                        var data = {phase: 2, M1: M1};
                         $("#output").append($("<div />").html("SEND: " + JSON.stringify(data)));
                         $.ajax({
                                url: "server.php",
                                method: 'GET',
                                dataType: 'json',
                                data: data,
                                success: function(res){
                                    $("#output").append($("<div />").html("Receive " + JSON.stringify(res)));
                                    
                                    if(!res.success){
                                        $("#output").append($("<div />").html("PASSWORD WRONG"));
                                        return;
                                    }
                                    
                                    var M2 = res.M2;

                                    var M2_check = me.srp.generateM2(A, M1, S);
                                    
                                    if(M2 == M2_check){
                                        $("#output").append($("<div />").html("Server verification complete " + me.srp.generateK(S)));
                                    } else {
                                        $("#output").append($("<div />").html("SERVER NOT VERIFICATED"));
                                    }
                                }

                            })
                    }
                    
                };
              
              $("#btRegistrate").click(function(){
                  demo.registrate($("#username").val(), $("#password").val())
                  return false;
              })
              
              $("#btLogin").click(function(){
                  demo.loginPhase1($("#username").val(), $("#password").val());
                  return false;
              })
            
        </script>
    </body>
</html>
