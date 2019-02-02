<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	class fms_listing_statuses {
		
		/*
		public static $NONE				= "none";
		public static $ACTIVE_SALE		= "active_sale";
		public static $ACTIVE_LEASE		= "active_lease";
		public static $CONDITIONAL		= "conditional";
		public static $SOLD				= "sold";
		public static $LEASED			= "leased";
		public static $CANCELLED		= "cancelled";
		public static $EXPIRED			= "expired";
		*/
		
		// The new way!
		// This was added so that we can sort listings by status hierarchy more easily
		
		public static $NONE				= "0";
		public static $ACTIVE_SALE		= "1";
		public static $ACTIVE_LEASE		= "2";
		public static $CONDITIONAL		= "3";
		public static $SOLD				= "4";
		public static $LEASED			= "5";
		public static $CANCELLED		= "6";
		public static $EXPIRED			= "7";
	}
	
?>