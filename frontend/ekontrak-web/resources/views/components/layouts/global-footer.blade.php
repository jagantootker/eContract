<style>
    .ek-footer {
        border-top: 1px solid #dbe3ef;
        background: #f8fafc;
        color: #334155;
        width: 100%;
    }

    .ek-footer__inner {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0.4rem 1.25rem;
    }

    .ek-footer__public {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.35rem 1rem;
        align-items: center;
    }

    .ek-footer__public-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem 0.9rem;
        min-width: 0;
    }

    .ek-footer__item {
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        font-size: 0.68rem;
        line-height: 1.25;
        white-space: nowrap;
    }

    .ek-footer__item svg {
        width: 12px;
        height: 12px;
        color: #64748b;
        flex-shrink: 0;
    }

    .ek-footer__link {
        color: #1d4ed8;
        text-decoration: none;
    }

    .ek-footer__link:hover {
        text-decoration: underline;
    }

    .ek-footer__brand {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        justify-self: end;
        min-width: 0;
    }

    .ek-footer__brand img {
        width: 16px;
        height: 16px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .ek-footer__copy {
        font-size: 0.68rem;
        line-height: 1.25;
        color: #475569;
        white-space: nowrap;
    }

    .ek-footer--internal {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 120;
        box-shadow: 0 -1px 0 rgba(219, 227, 239, 0.9), 0 -10px 24px rgba(15, 23, 42, 0.06);
    }

    .ek-footer--internal .ek-footer__inner {
        max-width: none;
        padding-top: 0.3rem;
        padding-bottom: 0.3rem;
    }

    .ek-footer--internal .ek-footer__internal {
        min-height: 42px;
    }

    .ek-footer__internal {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        text-align: center;
        min-height: 40px;
    }

    .ek-footer__internal img {
        width: 14px;
        height: 14px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .ek-footer__internal span {
        font-size: 0.68rem;
        line-height: 1.25;
        color: #475569;
    }

    @media (max-width: 768px) {
        .ek-footer__inner {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        .ek-footer__public {
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }

        .ek-footer__public-row {
            gap: 0.25rem 0.75rem;
        }

        .ek-footer__brand {
            justify-self: start;
        }

        .ek-footer__copy {
            white-space: normal;
        }
    }
</style>

@php($footerVariant = $variant ?? 'public')

<footer class="ek-footer {{ $footerVariant === 'internal' ? 'ek-footer--internal' : 'ek-footer--public' }}" role="contentinfo">
    <div class="ek-footer__inner">
        @if($footerVariant === 'internal')
            <div class="ek-footer__internal">
                <img src="{{ asset('storage/images/JATA_KPKT_BM_BLACK.png') }}" alt="KPKT">
                <span>Hak Cipta Terpelihara &copy; {{ date('Y') }} KPKT</span>
            </div>
        @else
            <div class="ek-footer__public">
                <div class="ek-footer__public-row" aria-label="Maklumat hubungan">
                    <div class="ek-footer__item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18M7 3v4m10-4v4M5 9h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2z"/>
                        </svg>
                        <span>Isnin – Jumaat, 8:00 pagi – 5:00 petang</span>
                    </div>

                    <div class="ek-footer__item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a2 2 0 011.9 1.37l1.02 3.07a2 2 0 01-.45 2.05l-1.4 1.4a16.02 16.02 0 006.36 6.36l1.4-1.4a2 2 0 012.05-.45l3.07 1.02A2 2 0 0121 18.72V22a2 2 0 01-2 2h-1C9.16 24 0 14.84 0 4V3a2 2 0 012-2h1z"/>
                        </svg>
                        <span>03-8891 5000</span>
                    </div>

                    <div class="ek-footer__item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 4H8m12 4H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                        </svg>
                        <a class="ek-footer__link" href="mailto:helpdesk.ekontrak@kpkt.gov.my">helpdesk.ekontrak@kpkt.gov.my</a>
                    </div>
                </div>

                <div class="ek-footer__brand">
                    <img src="{{ asset('storage/images/JATA_KPKT_BM_BLACK.png') }}" alt="KPKT">
                    <div class="ek-footer__copy">Hak Cipta Terpelihara &copy; {{ date('Y') }} KPKT</div>
                </div>
            </div>
        @endif
    </div>
</footer>
