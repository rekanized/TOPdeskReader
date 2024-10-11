@extends('layouts.layout')

@section('content')
    <div style="padding: 20px;display: flex;flex-direction: column;align-items: center;">
        <h1>{{ $ticket['naam'] }}</h1>
        <div style="font-size: 12px;position: absolute">{{ $ticket['unid'] }}</div>
        <div style="display: flex;padding-bottom: 60px;border-bottom: 1px solid #d5d3d3">
            <div id="carddata">
                <div class="title">Card</div>
                <div class="fielddata"><p><b>Company</b></p><input type="text" value="{{ $ticket['ref_vestiging'] }}" disabled /></div>
                <div class="fielddata"><p><b>Person</b></p><input type="text" value="{{ $ticket['aanmeldernaam'] }}" disabled /></div>
                <div class="fielddata"><p><b>Email</b></p><input type="text" value="{{ $ticket['aanmelderemail'] }}" disabled /></div>
                <div class="title">Ticket</div>
                <div class="fielddata"><p><b>Brief description</b></p><input type="text" value="{{ $ticket['korteomschrijving'] }}" disabled /></div>
                <div class="fielddata"><p><b>Ticket type</b></p><input type="text" value="{{ $ticket['ref_soortmelding'] }}" disabled /></div>
                <div class="fielddata"><p><b>Categorization</b></p><input type="text" value="{{ $ticket['ref_domein'] . ' > ' . $ticket['ref_specificatie'] }}" disabled /></div>
                <div class="fielddata"><p><b>Impact</b></p><input type="text"  value="{{ $ticket['ref_impact'] }}" disabled /></div>
                <div class="fielddata"><p><b>Creation date</b></p><input type="text"  value="{{ $ticket['dataanmk'] }}" disabled /></div>
            </div>
            <div style="display: flex;padding: 0px 50px;border-left: 1px solid #d5d3d3;flex-direction: column;">
                <p style="padding: 0px 16px"><b>Request</b></p>
                <textarea style="min-width: 50vw;min-height: 500px;margin: 4px;" disabled >{{ $ticket['verzoek'] }}</textarea>
            </div>
        </div>
        <div style="width: 80vw;padding: 20px 0px">
            <h2>Comments</h2>
            <div id="comments"></div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $.ajax({
                url: '/api/comments?unid={{ $ticket['unid'] }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    
                    let commentsContainer = document.querySelector('#comments');

                    data.forEach(function(comment) {

                        let commentContainer = document.createElement('div');
                        let commentMessage = document.createElement('div');
                        commentMessage.style.borderBottom = "1px solid grey";
                        commentMessage.style.padding = "16px 0px";
                        commentMessage.style.marginBottom = "8px";

                        if (comment.invisibleforcaller == true) {
                            let invisibleForCaller = document.createElement('div');
                            invisibleForCaller.classList.add('invisibleforcaller');
                            invisibleForCaller.appendChild(document.createTextNode('Invisible for caller'));
                            commentMessage.appendChild(invisibleForCaller);
                        }

                        commentMessage.innerHTML += '<div>'+comment.memotekst.replaceAll('\n','<br>')+'</div>';

                        let commenter = document.createElement('div');
                        commenter.appendChild(document.createTextNode(comment.naam));

                        let commentDate = document.createElement('span');
                        commentDate.style.fontSize = "12px";
                        commentDate.appendChild(document.createTextNode(comment.dataanmk));

                        commentContainer.appendChild(commentMessage);
                        commentContainer.appendChild(commenter);
                        commentContainer.appendChild(commentDate);

                        commentsContainer.appendChild(commentContainer);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Handle the error response
                    console.error('Error:', textStatus, errorThrown);
                    $('#comments').html('<p>An error occurred while fetching data.</p>');
                }
            });
        });
    </script>
@endsection