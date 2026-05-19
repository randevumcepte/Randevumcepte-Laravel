<?php

namespace App\Services;

/**
 * Bildirim tipleri. Flutter NotificationRouter ile aynı string'leri kullanır.
 * Yeni bir tip eklendiğinde Flutter tarafında da NotificationRouter'a eklenmeli.
 */
class NotificationTypes
{
    // Randevu
    public const APPOINTMENT_CREATED       = 'appointment_created';
    public const APPOINTMENT_APPROVED      = 'appointment_approved';
    public const APPOINTMENT_CANCELLED     = 'appointment_cancelled';
    public const APPOINTMENT_TIME_CHANGED  = 'appointment_time_changed';
    public const APPOINTMENT_REMINDER      = 'appointment_reminder';
    public const APPOINTMENT_REMINDER_HOUR = 'appointment_reminder_hour';
    public const STAFF_ASSIGNED            = 'staff_assigned';

    // Seans / paket
    public const SESSION_REMINDER          = 'session_reminder';
    public const SESSION_USED              = 'session_used';

    // Ödeme & mesaj
    public const PAYMENT_RECEIVED          = 'payment_received';
    public const NEW_MESSAGE               = 'new_message';

    // Pazarlama (rich + popup)
    public const CAMPAIGN                  = 'campaign';
    public const DISCOUNT                  = 'discount';
    public const WHEEL_CHANCE              = 'wheel_chance';
    public const BIRTHDAY                  = 'birthday';

    // Geri besleme
    public const SURVEY                    = 'survey';

    // Sistem
    public const MEMBERSHIP_EXPIRING       = 'membership_expiring';
    public const SYSTEM_ANNOUNCEMENT       = 'system_announcement';
    // Personelin yetkileri yonetici tarafindan guncellendi.
    // Flutter tarafi alinca: Yetki.tazele + popup + logout zorlamasi.
    public const YETKI_DEGISTI             = 'yetki_degisti';

    /**
     * Bu tipler resimli + popup gösterilmeli (foreground'da büyük dialog).
     */
    public static function isPopup(string $type): bool
    {
        return in_array($type, [
            self::CAMPAIGN,
            self::DISCOUNT,
            self::WHEEL_CHANCE,
            self::BIRTHDAY,
        ], true);
    }

    /**
     * Yüksek öncelikli (titreşim, ses, ekran yakma).
     */
    public static function isHighPriority(string $type): bool
    {
        return in_array($type, [
            self::APPOINTMENT_REMINDER,
            self::APPOINTMENT_REMINDER_HOUR,
            self::APPOINTMENT_TIME_CHANGED,
            self::APPOINTMENT_CANCELLED,
            self::SESSION_REMINDER,
            self::SESSION_USED,
            self::NEW_MESSAGE,
            self::PAYMENT_RECEIVED,
        ], true);
    }
}
