@extends('layouts.superadmin')

@section('title', 'Dashboard')

@section('content')
<div class="sa-animate-fade-up">
    <!-- ═════════════════════════════════════════════════════════════════════
         ONLY 4 METRIC CARDS (NO LINKS, ALL NAVIGATION VIA SIDEBAR)
         ═════════════════════════════════════════════════════════════════════ -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">

        <!-- 1. Total Enrollment -->
        <div class="sa-gradient-card" style="background: linear-gradient(135deg, #1B3A6B 0%, #2563eb 100%); box-shadow: 0 10px 25px -5px rgba(27, 58, 107, 0.4); border-radius: 18px; padding: 28px; position: relative; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease; color: #ffffff;">
            <!-- Decorative circles -->
            <div style="position: absolute; right: -25px; bottom: -25px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%); pointer-events: none;"></div>
            <div style="position: absolute; right: 25px; top: -30px; width: 90px; height: 90px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>

            <div style="font-size: 46px; font-weight: 800; line-height: 1; letter-spacing: -1px; margin-bottom: 8px; color: #ffffff;" data-counter-target="{{ $totalEnrollment }}">
                {{ number_format($totalEnrollment) }}
            </div>
            <div style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255, 255, 255, 0.85);">
                Total Enrollment
            </div>
        </div>

        <!-- 2. Total Exams -->
        <div class="sa-gradient-card" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4); border-radius: 18px; padding: 28px; position: relative; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease; color: #ffffff;">
            <!-- Decorative circles -->
            <div style="position: absolute; right: -25px; bottom: -25px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%); pointer-events: none;"></div>
            <div style="position: absolute; right: 25px; top: -30px; width: 90px; height: 90px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
            </div>

            <div style="font-size: 46px; font-weight: 800; line-height: 1; letter-spacing: -1px; margin-bottom: 8px; color: #ffffff;" data-counter-target="{{ $totalExams }}">
                {{ number_format($totalExams) }}
            </div>
            <div style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255, 255, 255, 0.85);">
                Total Exams
            </div>
        </div>

        <!-- 3. Fees Paid -->
        <div class="sa-gradient-card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.4); border-radius: 18px; padding: 28px; position: relative; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease; color: #ffffff;">
            <!-- Decorative circles -->
            <div style="position: absolute; right: -25px; bottom: -25px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%); pointer-events: none;"></div>
            <div style="position: absolute; right: 25px; top: -30px; width: 90px; height: 90px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>

            <div style="font-size: 46px; font-weight: 800; line-height: 1; letter-spacing: -1px; margin-bottom: 8px; color: #ffffff;" data-counter-target="{{ $feesPaid }}" data-currency="true">
                Rs {{ number_format($feesPaid) }}
            </div>
            <div style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255, 255, 255, 0.85);">
                Fees Paid
            </div>
        </div>

        <!-- 4. Fees Payable -->
        <div class="sa-gradient-card" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.4); border-radius: 18px; padding: 28px; position: relative; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease; color: #ffffff;">
            <!-- Decorative circles -->
            <div style="position: absolute; right: -25px; bottom: -25px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%); pointer-events: none;"></div>
            <div style="position: absolute; right: 25px; top: -30px; width: 90px; height: 90px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
            </div>

            <div style="font-size: 46px; font-weight: 800; line-height: 1; letter-spacing: -1px; margin-bottom: 8px; color: #ffffff;" data-counter-target="{{ $feesPayable }}" data-currency="true">
                Rs {{ number_format($feesPayable) }}
            </div>
            <div style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255, 255, 255, 0.85);">
                Fees Payable
            </div>
        </div>

    </div>
</div>
@endsection
