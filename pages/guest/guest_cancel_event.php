<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php");
$guest_id=(int)$_SESSION['guest_id']; $event_id=(int)($_GET['event_id']??0); $hotel_id=(int)($_GET['hotel_id']??0);
$t="hotel{$hotel_id}_event{$event_id}"; $conn->query("DELETE FROM $t WHERE guest_id=$guest_id");
header("Location: guest_hotel_view.php?hotel_id=$hotel_id"); exit();
