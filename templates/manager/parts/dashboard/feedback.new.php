<?php
if (isset($data['feedback']) && count($data['feedback']) > 0) {
?>
    <div class="col-12 col-lg-6">
        <div class="card h-100 hover-shadow transition-300" data-bs-toggle="modal" data-bs-target="#feedbacksModal" onclick="loadFeedbacks(1)">
            <span class="badge badge-pill" style="background-color:rgb(245, 136, 11); width:70px">Info</span>
            <div class="card-body text-center d-flex flex-column">
                <div class="mb-3">
                </div>
                <?php
                $name = $data['feedback'][0]['name'];
                $message = $data['feedback'][0]['message'];
                $date = $data['feedback'][0]['sent_at'];
                ?>
                <h6 class="card-subtitle text-muted mb-2">Un visiteur nommé <strong><?php echo $name; ?></strong> vous a laissé un feedback</h6>
                <p class="text-muted small mt-2">Date : <?php echo $date; ?></p>
                <div class="message-preview mt-2 mb-3">
                    <p class="message-text"><?php echo $message; ?></p>
                </div>
                <div class="mt-auto pt-3">
                    <span class="btn btn-sm btn-outline-primary w-100">Voir les feedbacks</span>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>