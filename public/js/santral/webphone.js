/*
** SIP Demo using sip.js 0.20
*/

// SIP Server
defaultSIPServer = "santral2.randevumcepte.com.tr";

// WebSocket Server URL
webSocketServer = `wss://santral2.randevumcepte.com.tr:8089/ws`;

// get interactive elements
serverSpan = document.getElementById("server");
targetSpan = document.getElementById("target");
callButton = document.getElementById("call");
callButton2 = document.getElementById("aramayapyazi1");
callButton3 = document.getElementById("aramayapyazi2");
answerButton = document.getElementById("answer");
answerButton2 = document.getElementById("cevaplayazi1");
answerButton3 = document.getElementById("cevaplayazi2");
hangupButton = document.getElementById("hangup");
hangupButton2 = document.getElementById("kapatyazi1");
hangupButton3 = document.getElementById("kapatyazi2");
audioElement = document.getElementById("remoteAudio");
keypad = document.querySelectorAll(".keypad");
dtmfSpan = document.getElementById("dtmf");
holdButton = document.getElementById("hold");
//muteButton = document.getElementById("mute");
holdButton2 = document.getElementById("sesikapatyazi1");
holdButton3 = document.getElementById("sesikapatyazi2");
dialInput = document.getElementById("dial");
ringTone = document.getElementById("ringtone");
ringbackTone = document.getElementById("ringbacktone");
dtmfTone = document.getElementById("dtmfTone");
pbdial = document.querySelectorAll(".pbdial");

dahili = $('#santral_dahili_no').val();
dahiliSifre = $('#santral_dahili_sifre').val();

aramaListesiId = 0;
callId = "";
callerNumber = "";
extension = "";
cevaplamaZamani = "";
/*
** Setup SIP
*/


// SimpleUser options
simpleUserOptions = {
    delegate: {
        onCallCreated:      function(session) { callCreate(session); },
        onCallAnswered:     function(session) { callAnswer(session); },
        onCallHangup:       function(session) { callHangUp(session); },
        onCallReceived:     function(session) { callReceived(session); },
        onCallHold:         function(held)    { callHold(held); },
        onServerConnect:    function()        { serverConnect(); },
        onServerDisconnect: function(error)   { serverDisconnect(error); }
    },
    media: {
        remote: {
            audio: audioElement
        }
    },
    aor: `sip:${dahili}@${defaultSIPServer}`,
    userAgentOptions: {
        logLevel: "debug",
        displayName: dahili,
        authorizationUsername: dahili,
        authorizationPassword: dahiliSifre
    }
};

// SimpleUser construction
var simpleUser = new SIP.Web.SimpleUser(webSocketServer, simpleUserOptions);

/*
** Connect SIP on Load
*/

// connect
callButton.disabled = true;
hangupButton.disabled = true;
simpleUser.connect().then(function()
{
    callButton.disabled = false;
    hangupButton.disabled = true;
    serverSpan.innerHTML = defaultSIPServer;
})
.catch(function(error)
{
    console.error(`[${simpleUser.id}] failed to connect`);
    console.error(error);
    targetSpan.innerHTML = "Sunucuya bağlanamadı: " + error;
});

/*
** Event Listeners
*/

// Add click listeners to keypad buttons
/*keypad.forEach(function(button)
{
    button.addEventListener("click", function()
    {
        tone = button.textContent;
        if (tone)
        {
            playdtmfTone();
            simpleUser.sendDTMF(tone).then(function()
            {
                dtmfSpan.innerHTML += tone;
            });
        }
    });
});*/

// phonebook dial
pbdial.forEach(function(button)
{
    button.addEventListener("click", function()
    {
        var target = button.value;
        makeCall(target);
    });
});

// on enter key in dial
dialInput.addEventListener("keyup", function(event)
{
    if (event.keyCode === 13)
    {
        event.preventDefault();
        var target = dialInput.value;
        makeCall(target);
    }
});

// Add click listener to call button
callButton.addEventListener("click", function()
{
    var target = dialInput.value;

    makeCall(target);
});
callButton2.addEventListener("click", function()
{
    var target = dialInput.value;
    makeCall(target);
});
callButton3.addEventListener("click", function()
{
    var target = dialInput.value;
    makeCall(target);
});

// Add click listener to answer button
answerButton.addEventListener("click", function()
{
    // just answer for now
    simpleUser.answer().then(function()
    {
        answerButton.disabled = true;
        answerButton2.disabled = true;
        answerButton3.disabled = true;
        callButton.disabled = true;
        callButton2.disabled = true;
        callButton3.disabled = true;
        hangupButton.disabled = false;
        hangupButton2.disabled = false;
        hangupButton3.disabled = false;
        keypadDisabled(false);
    })
    .catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to answer`);
        console.error(error);
        alert("Failed to answer\n" + error);
    });
    
});

answerButton2.addEventListener("click", function()
{
    // just answer for now
    simpleUser.answer().then(function()
    {
        answerButton.disabled = true;
        answerButton2.disabled = true;
        answerButton3.disabled = true;
        callButton.disabled = true;
        callButton2.disabled = true;
        callButton3.disabled = true;
        hangupButton.disabled = false;
        hangupButton2.disabled = false;
        hangupButton3.disabled = false;
        keypadDisabled(false);
    })
    .catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to answer`);
        console.error(error);
        alert("Failed to answer\n" + error);
    });
    
});
answerButton3.addEventListener("click", function()
{
    // just answer for now
    simpleUser.answer().then(function()
    {
        answerButton.disabled = true;
        answerButton2.disabled = true;
        answerButton3.disabled = true;
        callButton.disabled = true;
        callButton2.disabled = true;
        callButton3.disabled = true;
        hangupButton.disabled = false;
        hangupButton2.disabled = false;
        hangupButton3.disabled = false;
        keypadDisabled(false);
    })
    .catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to answer`);
        console.error(error);
        alert("Failed to answer\n" + error);
    });
    
});


// Add click listener to hangup button
hangupButton.addEventListener("click", function()
{
    callButton.disabled = true;
    hangupButton.disabled = true;

    //muteButtonToggle(false);
    holdButtonToggle(false);
    simpleUser.hangup().catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to hangup call`);
        console.error(error);
        alert("Failed to hangup call.\n" + error);
    });
});
hangupButton2.addEventListener("click", function()
{
    callButton.disabled = true;
    hangupButton.disabled = true;
    //muteButtonToggle(false);
    holdButtonToggle(false);
    simpleUser.hangup().catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to hangup call`);
        console.error(error);
        alert("Failed to hangup call.\n" + error);
    });
});
hangupButton3.addEventListener("click", function()
{
    callButton.disabled = true;
    hangupButton.disabled = true;
    //muteButtonToggle(false);
    holdButtonToggle(false);
    simpleUser.hangup().catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to hangup call`);
        console.error(error);
        alert("Failed to hangup call.\n" + error);
    });
});

// Add change listener to hold checkbox
holdButton.addEventListener("click", function()
{
    if (holdButton.value == "hold")
    {
        // un-hold
        holdButtonToggle(false);
       
        simpleUser.unhold().catch(function(error)
        {
            console.error(`[${simpleUser.id}] failed to unhold call`);
            console.error(error);
            alert("Failed to unhold call.\n" + error);
        });
    }
    else
    {
        // hold
        holdButtonToggle(true);

        simpleUser.hold().catch(function(error)
        {

            console.error(`[${simpleUser.id}] failed to hold call`);
            console.error(error);
            alert("Failed to hold call.\n" + error);
        });
    }
});
holdButton2.addEventListener("click", function()
{
    if (holdButton.value == "hold")
    {
        // un-hold
        holdButtonToggle(false);
       
        simpleUser.unhold().catch(function(error)
        {
            console.error(`[${simpleUser.id}] failed to unhold call`);
            console.error(error);
            alert("Failed to unhold call.\n" + error);
        });
    }
    else
    {
        // hold
        holdButtonToggle(true);

        simpleUser.hold().catch(function(error)
        {

            console.error(`[${simpleUser.id}] failed to hold call`);
            console.error(error);
            alert("Failed to hold call.\n" + error);
        });
    }
});
holdButton3.addEventListener("click", function()
{
    if (holdButton.value == "hold")
    {
        // un-hold
        holdButtonToggle(false);
       
        simpleUser.unhold().catch(function(error)
        {
            console.error(`[${simpleUser.id}] failed to unhold call`);
            console.error(error);
            alert("Failed to unhold call.\n" + error);
        });
    }
    else
    {
        // hold
        holdButtonToggle(true);

        simpleUser.hold().catch(function(error)
        {

            console.error(`[${simpleUser.id}] failed to hold call`);
            console.error(error);
            alert("Failed to hold call.\n" + error);
        });
    }
});

// Add change listener to mute checkbox
/*muteButton.addEventListener("click", function()
{
    if (muteButton.value == "mute")
    {
        // unmute
        simpleUser.unmute();
        muteButtonToggle(false);
        if (simpleUser.isMuted() === true)
        {
            console.error(`[${simpleUser.id}] failed to unmute call`);
            alert("Failed to unmute call.\n");
        }
    }
    else
    {
        // mute
        simpleUser.mute();
        muteButtonToggle(true);
        if (simpleUser.isMuted() === false)
        {
            console.error(`[${simpleUser.id}] failed to mute call`);
            alert("Failed to mute call.\n");
        }
    }
});
muteButton2.addEventListener("click", function()
{
    if (muteButton.value == "mute")
    {
        // unmute
        simpleUser.unmute();
        muteButtonToggle(false);
        if (simpleUser.isMuted() === true)
        {
            console.error(`[${simpleUser.id}] failed to unmute call`);
            alert("Failed to unmute call.\n");
        }
    }
    else
    {
        // mute
        simpleUser.mute();
        muteButtonToggle(true);
        if (simpleUser.isMuted() === false)
        {
            console.error(`[${simpleUser.id}] failed to mute call`);
            alert("Failed to mute call.\n");
        }
    }
});
muteButton3.addEventListener("click", function()
{
    if (muteButton.value == "mute")
    {
        // unmute
        simpleUser.unmute();
        muteButtonToggle(false);
        if (simpleUser.isMuted() === true)
        {
            console.error(`[${simpleUser.id}] failed to unmute call`);
            alert("Failed to unmute call.\n");
        }
    }
    else
    {
        // mute
        simpleUser.mute();
        muteButtonToggle(true);
        if (simpleUser.isMuted() === false)
        {
            console.error(`[${simpleUser.id}] failed to mute call`);
            alert("Failed to mute call.\n");
        }
    }
});*/


/*
** Helper Functions
*/

// dial a number and make a call
makeCall = function (target)
{
    if (!target || !target.match(/^[0-9]+$/))
    {
        console.log("invalid dial");
        return;
    }
    callButton.disabled = true;
    hangupButton.disabled = true;
    simpleUser.call(`sip:${target}@${defaultSIPServer}`,
    {
        inviteWithoutSdp: false
    })
    .catch(function(error)
    {
        console.error(`[${simpleUser.id}] failed to place call`);
        console.error(error);
        alert("Failed to place call.\n" + error);
    });
};

// Keypad helper function
keypadDisabled = function (disabled)
{
    keypad.forEach(function(button)
    {
        button.disabled = disabled
    });
    dtmfSpan.innerHTML = "";
};

// Hold helper function
holdButtonToggle = function (down)
{
    if (down)
    {
        targetSpan.innerHTML = targetSpan.innerHTML + "(Beklemede)";
        holdButton.value = "hold";
        holdButton.classList.add("btn-primary");
        //$('#call').attr('style','display:inline-block');
    }
    else
    {
        targetSpan.innerHTML = targetSpan.innerHTML.replace(/ \(HOLD\)/,'');
        holdButton.value = "";
        holdButton.classList.remove("btn-primary");
        //$('#call').attr('style','display:inline-block');
    }
};

// Mute helper function
/*muteButtonToggle = function (down)
{
    if (down)
    {
        muteButton.value = "mute";
        muteButton.classList.add("btn-primary");
    }
    else
    {
        muteButton.value = "";
        muteButton.classList.remove("btn-primary");
    }
};*/


/*
** Call Hanlders (Delegates)
*/

// on call created
callCreate = function()
{
    console.log(`Arayan veya aranan telefon no : ${dialInput.value}`);
    callButton.disabled = true;
    hangupButton.disabled = false;
    hangupButton2.disabled = false;
    hangupButton3.disabled = false;
    answerButton.disabled = true;
    answerButton2.disabled = true;
    answerButton3.disabled = true;
    keypadDisabled(true);
    holdButtonToggle(false);
    //muteButtonToggle(false);
    startRingbackTone();

    $.ajax({
                type:"GET",
                url:'/isletmeyonetim/musteriarama',
                dataType:"text",
                data:{telefon:`${dialInput.value}`,sube:$('input[name="sube"]').val()},
               
                
                success:function(result){
                        

                    if(dialInput.value != '')
                    {
                        targetSpan.innerHTML = `Aranıyor: `+result;
                         if(!$('#webTelefonDropDown').hasClass('show'))
                           {
                             $('#webtelefon').trigger('click');
                           }
                        
                    }
                    
                   
                  

                },
                 error: function (request, status, error) {
                    $('#hata').empty();
                   tmp = request.responseText;
                    document.getElementById('hata').innerHTML = request.responseText;
                }


    });
    //targetSpan.innerHTML = `Aranıyor: ${dialInput.value}`;
};

// on call answered
callAnswer = function(session)
{

    
    console.log(`[${simpleUserOptions.userAgentOptions.displayName}] Call answered`);
    console.log("Call id session id "+simpleUser.session.id);
    keypadDisabled(true);
    holdButtonToggle(false);

    //muteButtonToggle(false);
    //muteButton.disabled = false;
    holdButton.disabled = false;
    callButton.disabled = true;
    hangupButton.disabled = false;
    stopRingTone();
    stopRingbackTone();

    //console.log(simpleUser.session);
     $.ajax({
                type:"GET",
                url:'/isletmeyonetim/musteriarama',
                dataType:"text",
                data:{telefon:`${simpleUser.session.remoteIdentity.uri.user}`,sube:$('input[name="sube"]').val()},
               
                
                success:function(result){
                    

                    targetSpan.innerHTML = `Bağlandı: `+result;
                    //$('#webtelefon').trigger('click');
                  
                  

                },
                 error: function (request, status, error) {
                    $('#hata').empty();
                   tmp = request.responseText;
                    document.getElementById('hata').innerHTML = request.responseText;
                }


    });

    callId = simpleUser.session.id; // SIP Call-ID
    callerNumber = simpleUser.session.remoteIdentity.uri.user; // Arayan numara
    extension = simpleUser.session.localIdentity.uri.user; // Dahili numara
    cevaplamaZamani = new Date().toISOString();
    console.log(simpleUser.session.id+" "+simpleUser.session.remoteIdentity.uri.user+" "+simpleUser.session.localIdentity.uri.user+" "+new Date().toISOString());
    // FreePBX/DB'ye kayıt eşleştirmesi için AJAX
    

    //targetSpan.innerHTML = `Bağlandı: ${simpleUser.session.remoteIdentity.uri.user}`;
};


// on call hang up
callHangUp = function()
{
    console.log("ARama sonlandı : "+callId+" - "+callerNumber+" - "+ extension+" - "+cevaplamaZamani);
    console.log(`[${simpleUserOptions.userAgentOptions.displayName}] Call hangup`);
    callButton.disabled = false;
    callButton2.disabled = false;
    callButton3.disabled = false;
    hangupButton.disabled = true;
    hangupButton2.disabled = true;
    hangupButton3.disabled = true;
    answerButton.disabled = true;
    answerButton2.disabled = true;
    answerButton3.disabled = true;
    answerButton.classList.remove("btn-primary");
    keypadDisabled(false);
    holdButtonToggle(false);
    //muteButtonToggle(false);
    //muteButton.disabled = true;
    holdButton.disabled = true;
    holdButton2.disabled = true;
    holdButton3.disabled = true;
    stopRingTone();
    stopRingbackTone();
    targetSpan.innerHTML = `Bağlandı. Dahili : `+$('#santral_dahili_no').val();
    dialInput.value = "";
    var disabledAramaListeLink = $('a[name="arama_liste_detaylari"][disabled]').first();

    if(disabledAramaListeLink.length)
    {
        $.ajax({
            type: "POST",
            url: "https://santral.randevumcepte.com.tr/monitor/sesKaydinaUlas.php", // Özel bir endpoint
            data: {
                call_id: callId,
                caller: callerNumber,
                extension: extension,
                timestamp: cevaplamaZamani,
            },
            success: function(response) {
                console.log("response : ",response);
                console.log("Arama için ses kaydı bulundu:", response.recording_path);
                if(response.status === 'success' && response.recording_path) {
                    const recordingPath = response.recording_path;
            
                    const aramaId = disabledAramaListeLink.attr('data-value');
                    console.log("liste id "+aramaId);

                    $.ajax({
                        type: "POST",
                        url: "/isletmeyonetim/aramaListesineSesKaydiEkle", // Kendi endpoint'iniz
                        data: {
                            aramaListeId: aramaId,
                            arananNo: callerNumber,
                            sesKaydi: recordingPath
                        },
                        headers:{
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        },
                        success: function(dbResponse) {
                            console.log("Ses kaydı veritabanına kaydedildi:", dbResponse);
                        },
                        error: function(xhr, status, error) {
                            console.error("Veritabanı kaydı hatası:", error);
                        }
                    });
                }


            },
            error: function(xhr, status, error) {
                console.error("Ses kaydı bulunurken bir hata oluştu :", error);
            }
        });

        disabledAramaListeLink.removeAttr('disabled');
        disabledAramaListeLink.trigger('click');
    }
    $('#answer').attr('style','display:none');
    $('#call').attr('style','display:inline-block');
    $('#hangup').attr('disabled','true');
    $('#hold').attr('disabled','true');
};

// on call hold
callHold = function(held)
{
    console.log(`[${simpleUserOptions.userAgentOptions.displayName}] Call hold ${held}`);
};

// incomming call
callReceived = function(session)
{
    answerButton.disabled = false;
    answerButton2.disabled = false;
    answerButton3.disabled = false;
    hangupButton.disabled = false;
    hangupButton2.disabled = false;
    hangupButton3.disabled = false;
    holdButton.disabled = true;
    holdButton2.disabled = true;
    holdButton3.disabled = true;
    $('#answer').attr('style','display:inline-block');
    $('#call').attr('style','display:none');
    console.log(`Gelen arama arayan numara : ${simpleUser.session.remoteIdentity.uri.user}`)
    answerButton.classList.add("btn-success");
    var return_first = function () {
        var tmp = null;
        $.ajax({
                type:"GET",
                url:'/isletmeyonetim/musteriarama',
                dataType:"text",
                data:{telefon:`${simpleUser.session.remoteIdentity.uri.user}`,sube:$('input[name="sube"]').val()},
               
                
                success:function(result){
                   
                    console.log('arama geldi web telefon açık : '+$('#webTelefonDropDown').hasClass('show'));
                    targetSpan.innerHTML = `Gelen Arama: `+result;

                     if(!$('#webtelefon').prop('aria-expanded')||$('#webtelefon').prop('aria-expanded')=='false')
                       {
                         $('#webtelefon').trigger('click');
                       }
                   
                  

                },
                 error: function (request, status, error) {
                    $('#hata').empty();
                    
                    document.getElementById('hata').innerHTML = request.responseText;
                }


        });
        return tmp;
    }(); 
    startRingTone();
};

// server is connected
serverConnect = function()
{
    // update display
    targetSpan.innerHTML = `Bağlandı. Dahili : `+$('#santral_dahili_no').val();

    // register to receive calls
    simpleUser.register();
    baglantidurumu(1);
};

// when server is disconnected
serverDisconnect = function(error)
{
    console.log(error);
    callButton.disabled = true;
    targetSpan.innerHTML = `Bağlantı Kesildi: ${error}`;
    baglantidurumu(0);
};


/*
** Sound functions
*/

startRingTone = function() {
    try { $('#telefonSesiniCal').trigger('click');  console.log("telefon çalıyor.");/*ringTone.play();*/ } catch (e) { console.error("telefon çalınamıyor. ",e);}
};

stopRingTone = function() {
    try { $('#telefonSesiniCalmayiDurdur').trigger('click');/*ringTone.pause();*/ } catch (e) {console.log("telefon çalma durdurulamıyor."); }
};

startRingbackTone = function() {
    try { ringbackTone.play(); } catch (e) { }
};

stopRingbackTone = function() {
    try { ringbackTone.pause(); } catch (e) { }
};

playdtmfTone = function () {
    try { dtmfTone.play(); } catch (e) { }
}

/*
** Window Handlers
*/

// force prompt to confirm leaving page
window.onbeforeunload = function()
{
    baglantidurumu(0);
};

// disconnect connection when leaving page
window.onunload = function()
{
    simpleUser.disconnect();
};
function baglantidurumu(durum)
{
    var cihaz = '';
    if(durum==1)
        cihaz = getDeviceInfo();
    $.ajax({    
                type:"POST",
                url:'/isletmeyonetim/dahilibaglandi',
                dataType:"text",
                data:{dahilino:dahili,_token:$('input[name="_token"]').val(),baglandi:durum,device:cihaz},
               
                
                success:function(result){
                        
                    

                },
                 error: function (request, status, error) {
                    $('#hata').empty(); 
                    document.getElementById('hata').innerHTML = request.responseText;
                }


    });
}
function getDeviceInfo()
{
    var module = {
        options: [],
        header: [navigator.platform, navigator.userAgent, navigator.appVersion, navigator.vendor, window.opera],
        dataos: [
            { name: 'Windows Phone', value: 'Windows Phone', version: 'OS' },
            { name: 'Windows', value: 'Win', version: 'NT' },
            { name: 'iPhone', value: 'iPhone', version: 'OS' },
            { name: 'iPad', value: 'iPad', version: 'OS' },
            { name: 'Kindle', value: 'Silk', version: 'Silk' },
            { name: 'Android', value: 'Android', version: 'Android' },
            { name: 'PlayBook', value: 'PlayBook', version: 'OS' },
            { name: 'BlackBerry', value: 'BlackBerry', version: '/' },
            { name: 'Macintosh', value: 'Mac', version: 'OS X' },
            { name: 'Linux', value: 'Linux', version: 'rv' },
            { name: 'Palm', value: 'Palm', version: 'PalmOS' }
        ],
        databrowser: [
            { name: 'Chrome', value: 'Chrome', version: 'Chrome' },
            { name: 'Firefox', value: 'Firefox', version: 'Firefox' },
            { name: 'Safari', value: 'Safari', version: 'Version' },
            { name: 'Internet Explorer', value: 'MSIE', version: 'MSIE' },
            { name: 'Opera', value: 'Opera', version: 'Opera' },
            { name: 'BlackBerry', value: 'CLDC', version: 'CLDC' },
            { name: 'Mozilla', value: 'Mozilla', version: 'Mozilla' }
        ],
        init: function () {
            var agent = this.header.join(' '),
                os = this.matchItem(agent, this.dataos),
                browser = this.matchItem(agent, this.databrowser);
            
            return { os: os, browser: browser };
        },
        matchItem: function (string, data) {
            var i = 0,
                j = 0,
                html = '',
                regex,
                regexv,
                match,
                matches,
                version;
            
            for (i = 0; i < data.length; i += 1) {
                regex = new RegExp(data[i].value, 'i');
                match = regex.test(string);
                if (match) {
                    regexv = new RegExp(data[i].version + '[- /:;]([\\d._]+)', 'i');
                    matches = string.match(regexv);
                    version = '';
                    if (matches) { if (matches[1]) { matches = matches[1]; } }
                    if (matches) {
                        matches = matches.split(/[._]+/);
                        for (j = 0; j < matches.length; j += 1) {
                            if (j === 0) {
                                version += matches[j] + '.';
                            } else {
                                version += matches[j];
                            }
                        }
                    } else {
                        version = '0';
                    }
                    return {
                        name: data[i].name,
                        version: parseFloat(version)
                    };
                }
            }
            return { name: 'unknown', version: 0 };
        }
    };
    
    var e = module.init(),
        debug = '';
    
    debug +=  e.os.name + ' ';
    debug +=  e.os.version + ' ';
    debug +=  e.browser.name + ' ';
    debug +=  e.browser.version;
    
     
    
    return debug;
}
// done
