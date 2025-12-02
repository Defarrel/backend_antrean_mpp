<?php

use Illuminate\Support\Facades\Broadcast;

// Izinkan semua orang mendengarkan channel ini
Broadcast::channel('queue-channel', function () {
    return true;
});