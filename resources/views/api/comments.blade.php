<?PHP 
    $data = [
        'requests' => $requests,
        'comments' => $comments
    ];
    echo json_encode($data);
?>