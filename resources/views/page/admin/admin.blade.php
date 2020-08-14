@extends('layouts.user')    

{{-- Content --}}
@section('content')
<div class="heading-text heading-section text-center">
	<!-- <h2>Monitoring</h2> -->
	<h2>Sensor Manager</h2>
</div>
<div class="container">
    <div class="row mb-5">
        @foreach ($elements as $element)
        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
            <div class="card mb-5">
                <div class="card-body">
                    <h5 id="element_title_{{ $element->id }}" class="sensor-title text-center">{{ $element->name }}</h5>
                    <!-- <form> -->
                        <div class="form-group row">
                            <input type="hidden" id="_{{ $element->id }}" value="{{ $element->id }}">
                            <label for="title" class="col-3 col-sm-3 col-md-3 col-lg-2 col-form-label col-form-label-lg">Title:</label>
                            <div class="col-9 col-sm-9 col-md-9 col-lg-10">
                                <input type="text" class="form-control form-control-lg" id="title_{{ $element->id }}" value="{{ $element->name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="unit" class="col-3 col-sm-3 col-md-3 col-lg-2 col-form-label col-form-label-lg">Unit:</label>
                            <div class="col-9 col-sm-9 col-md-9 col-lg-10">
                                <input type="text" class="form-control form-control-lg" id="unit_{{ $element->id }}" value="{{ $element->unit }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="enable" class="col-4 col-sm-4 col-md-4 col-lg-3 col-form-label col-form-label-lg">Enable:</label>
                            <div class="col-8 col-sm-8 col-md-8 col-lg-9">
                                <input id="enable_{{ $element->id }}"type="checkbox" @if($element->switched_on) checked @endif data-toggle="toggle" data-size="sm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reason_disabled" class="col-form-label col-form-label-lg">Reason for disabling:</label>
                            <textarea id="reason_{{ $element->id }}"class="form-control form-control-lg" id="readon_disabled" rows="3">{{ $element->reason_disabled }}</textarea>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary" type="buton" onclick="saveElements({{$element->id}})">Save</button>
                        </div>
                    <!-- </form> -->
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<script type="text/javascript" language="javascript">

    // Se creo la conexion a MQTT en un metodo debido a que no podia solo publicar, a fuerzas me pedia conectarme de nuevo.
    function mqttConnect(topic, payload) {
        // Create a client instance
        client = new Paho.MQTT.Client("broker.mqttdashboard.com", 8000, "clientId");

        // set callback handlers
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        // Connect options
        // connect the client
        client.connect({onSuccess:onConnect}); // GOOD

        // called when the client connects
        function onConnect() { // GOOD
            // Once a connection has been made, make a subscription and send a message.
            console.log("onConnected");
            client.subscribe(topic);
            message = new Paho.MQTT.Message(payload);
            message.destinationName = topic;
            client.send(message);
        }

        // called when the client loses its connection
        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log("onConnectionLost: " + responseObject.errorMessage);
                }
        }

        // called when a message arrives
        function onMessageArrived(message) {
            console.log("onMessageArrived: " + message.payloadString);
        }
    }

    $(document).ready(function() {
        mqttConnect("utt/0317114967", "Hello from document ready");
        $('a[href="/"]').removeClass("active");
        $('a[href="/dashboard"]').removeClass("active");
		$('a[href="/admin/settings"]').addClass("active");
		$('#textHeader').html("");
		if (isMobile.any()) {
			$('#headerInicio').css("height", "60vh");
        	$('#headerInicio').css("min-height", "20vh");
		} else {
			$('#headerInicio').css("height", "20vh");
        	$('#headerInicio').css("min-height", "auto");
		}
    });

    // Save changes for element.
    function saveElements(id) {
        var element = {
            id: $('#_' + id).val(),
            name: $('#title_' + id).val(),
            unit: $('#unit_' + id).val(),
            switched_on: $('#enable_' + id).is(":checked"),
            reason: $('#reason_' + id).val()
        }
        url = 'element/update';
        console.log($('#enable_' + id).is(":checked"));
        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: element,
            success: function(data, textStatus, xhr) {
                if( xhr.status === 200 ) {
                    console.log("Success");
                    // ADD HERE THE NEW SENSOR.
                    data = {
                        "temperature": ($('#enable_1').is(":checked")) ? 1 : 0,
                        "humidity": ($('#enable_2').is(":checked")) ? 1 : 0,
                        "carbonDioxide": ($('#enable_3').is(":checked")) ? 1 : 0
                    }
                    mqttConnect("utt/0317114967", JSON.stringify(data));
                } else {
                    console.log('%c Error: ', 'color:red;font-size:16px;', 'Error server.');
                }
            }
        });
    }
</script>
@endsection