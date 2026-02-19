@if(isset($adverts) && $adverts->count() > 0)
<div class="d-lg-none mt-3 mb-4">
    <div id="serviceAdvertCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            @foreach($adverts as $index => $advert)
                <button type="button" data-bs-target="#serviceAdvertCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner rounded-4 shadow-sm">
            @foreach($adverts as $index => $advert)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="advert-card position-relative overflow-hidden rounded-4" style="height: 180px; background: {{ $advert->image ? 'url(' . asset($advert->image) . ')' : 'linear-gradient(135deg, #4e73df 0%, #224abe 100%)' }}; background-size: cover; background-position: center;">
                        <div class="advert-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center px-4" style="background: rgba(0,0,0,0.4);">
                            @if($advert->service_name)
                                <span class="badge bg-warning text-dark mb-2 align-self-start text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                                    {{ $advert->service_name }}
                                </span>
                            @endif
                            
                            <h4 class="text-white fw-bold mb-1">{{ $advert->message }}</h4>
                            
                            @if($advert->discount)
                                <p class="text-white-50 mb-0 fw-medium">
                                    <i class="ti ti-gift me-1"></i> Get up to <span class="text-warning fw-bold">{{ $advert->discount }}</span> discount!
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($adverts->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#serviceAdvertCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#serviceAdvertCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
</div>

<style>
    #serviceAdvertCarousel .carousel-indicators {
        bottom: 10px;
    }
    #serviceAdvertCarousel .carousel-indicators [data-bs-target] {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin: 0 4px;
        background-color: rgba(255, 255, 255, 0.5);
        border: none;
    }
    #serviceAdvertCarousel .carousel-indicators .active {
        background-color: #fff;
        width: 20px;
        border-radius: 4px;
    }
    .advert-card {
        transition: transform 0.3s ease;
    }
    /* Simple fade animation for carousel items */
    #serviceAdvertCarousel .carousel-item {
        transition: transform 0.6s ease-in-out, opacity 0.6s ease-in-out;
    }
</style>
@endif
