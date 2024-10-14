@extends('layouts.layout')

@section('content')
    <div id="ticketcontainer">
        <div class="sectiontitle">{{ $ticket['naam'] }}</div>
        <div id="cardcontainer">
            <div id="carddata">
                <div class="title">Card</div>
                <div class="fielddata"><p><b>Company</b></p><input type="text" value="{{ $ticket['ref_vestiging'] }}" disabled /></div>
                <div class="fielddata"><p><b>Person</b></p><input type="text" value="{{ $ticket['aanmeldernaam'] }}" disabled /></div>
                <div class="fielddata"><p><b>Email</b></p><input type="text" value="{{ $ticket['aanmelderemail'] }}" disabled /></div>
                <div class="title">Ticket</div>
                <div class="fielddata"><p><b>Brief description</b></p><input type="text" value="{{ $ticket['korteomschrijving'] }}" disabled /></div>
                <div class="fielddata"><p><b>Ticket type</b></p><input type="text" value="{{ $ticket['ref_soortmelding'] }}" disabled /></div>
                <div class="fielddata"><p><b>Categorization</b></p><input type="text" value="{{ ($ticket['ref_domein']) != null ? $ticket['ref_domein'] . ' > ' . $ticket['ref_specificatie'] : "" }}" disabled /></div>
                <div class="fielddata"><p><b>Priority</b></p><input type="text"  value="{{ $ticket['impact'] . ' > ' . $ticket['urgency'] . ' > ' . $ticket['priority'] }}" disabled /></div>
                <div class="fielddata"><p><b>Creation date</b></p><input type="text"  value="{{ $ticket['dataanmk'] }}" disabled /></div>
            </div>
            <div id="request">
                <div class="title">Request</div>
                <div id="requestdata"></div>
            </div>
        </div>
        <div id="commentscontainer">
            <div class="sectiontitle">Comments</div>
            <div id="comments"></div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $.ajax({
                url: '/api/comments?unid={{ $ticket['unid'] }}&type=ticket',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    
                    let commentsContainer = document.querySelector('#comments');
                    let requestData = document.querySelector('#requestdata');

                    data['requests'].forEach(function(request){
                        let textArea = document.createElement('textarea');
                        textArea.disabled = true;
                        textArea.innerHTML = request.memotekst;
                        requestData.appendChild(textArea);
                    });

                    data['comments'].forEach(function(comment){

                        let commentContainer = document.createElement('div');
                        let commentMessage = document.createElement('div');
                        commentMessage.classList.add('comment');

                        if (comment.invisibleforcaller == true) {
                            let invisibleForCaller = document.createElement('div');
                            invisibleForCaller.classList.add('invisibleforcaller');
                            invisibleForCaller.appendChild(document.createTextNode('Invisible for caller'));
                            commentMessage.appendChild(invisibleForCaller);
                        }

                        if (comment.origin == 2){
                            commentContainer.classList.add('usercomment');
                        }

                        commentMessage.innerHTML += '<div>'+comment.memotekst.replaceAll('\n','<br>')+'</div>';

                        let commenter = document.createElement('div');
                        commenter.classList.add('commenter');
                        commenter.appendChild(document.createTextNode(comment.naam));

                        let commentDate = document.createElement('span');
                        commentDate.classList.add('commentdate');
                        commentDate.appendChild(document.createTextNode(comment.dataanmk));

                        commentContainer.appendChild(commentMessage);
                        commentContainer.appendChild(commenter);
                        commentContainer.appendChild(commentDate);

                        commentsContainer.appendChild(commentContainer);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown);
                    $('#comments').html('<p>An error occurred while fetching data.</p>');
                }
            });
        });
    </script>
@endsection