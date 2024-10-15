@extends('layouts.layout')

@section('content')
    <div id="ticketcontainer">
        <div class="sectiontitle">{{ $ticket['number'] }}</div>
        <div id="cardcontainer">
            <div id="carddata">
                <div class="title">Card</div>
                <div class="fielddata"><p><b>Main change</b></p><input type="text" value="{{ $ticket['ref_change_number'] }}" disabled /></div>
                <div class="fielddata"><p><b>Main description</b></p><input type="text" value="{{ $ticket['ref_change_brief_description'] }}" disabled /></div>
                <div class="fielddata"><p><b>Customer</b></p><input type="text" value="{{ $ticket['ref_caller_branch_name'] }}" disabled /></div>
                <div class="title">Ticket</div>
                <div class="fielddata"><p><b>Brief description</b></p><input type="text" value="{{ $ticket['briefdescription'] }}" disabled /></div>
                <div class="title">Processing</div>
                <div class="fielddata"><p><b>Operator</b></p><input type="text" value="{{ $ticket['ref_operatorname'] }}" disabled /></div>
                <div class="fielddata"><p><b>Status</b></p><input type="text" value="{{ $ticket['ref_status_name'] }}" disabled /></div>
                <div class="fielddata"><p><b>Resolved</b></p><input type="text" value="{{ $ticket['resolveddate'] }}" disabled /></div>
                <div class="fielddata"><p><b>Creation date</b></p><input type="text"  value="{{ $ticket['dataanmk'] }}" disabled /></div>
            </div>
            <div id="request">
                <div class="title">Request</div>
                <div id="requestdata"></div>
            </div>
        </div>
        <div id="relationscontainer">
            <div id="commentscontainer">
                <div class="sectiontitle">Comments</div>
                <div id="comments"></div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            let requestData = document.querySelector('#requestdata');
            loadJsonData('/api/requests?unid={{ $ticket['unid'] }}&type=changeactivity',requestData,function(data){
                data.forEach(function(request){
                    let textArea = document.createElement('textarea');
                    textArea.disabled = true;
                    textArea.innerHTML = request.memotekst;
                    requestData.appendChild(textArea);
                });
            });

            let commentsContainer = document.querySelector('#comments');
            loadJsonData('/api/comments?unid={{ $ticket['unid'] }}&type=changeactivity',commentsContainer,function(data) {

                data.forEach(function(comment){
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
            });
        });
    </script>
@endsection