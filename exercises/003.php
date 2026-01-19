<style>
  #url-table {
    width: 100%;
    max-width: 800px;
    margin: 20px auto;
    border-collapse: collapse;
  }
  #url-table th, #url-table td {
    border: 1px solid #ddd;
    padding: 8px;
    vertical-align: top;
  }
  #url-table th:first-child {
    width: 40px;
  }
  #url-table th:nth-child(2) {
    width: 30%;
  }
  #url-table th:nth-child(3) {
    width: 70%;
  }
  input[type="text"] {
    width: 100%;
    padding: 5px;
    box-sizing: border-box;
    margin: 0;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .correct {
    background-color: #ccffcc;
  }
  .incorrect {
    background-color: #ffcccc;
  }
  .instruction-cell {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>

<p>Kustuta igast URL-ist nõutud osa. Iga URL-i puhul on vaja kustutada erinev osa (alati kuni URL-i lõpuni). Tekstikast ei näita kogu URL-i, seega on soovitatav kasutada klaviatuuri kiirklahve (nt "vali kursori kohast kuni rea lõpuni" ja siis kustuta). Kui URL on õigesti muudetud, muutub tekstikasti taust roheliseks. Sul on aega 1 minut (60 sekundit).</p>
<form id="task-form">
  <table id="url-table">
    <thead>
      <tr><th>#</th><th>Juhend</th><th>URL</th></tr>
    </thead>
    <tbody></tbody>
  </table>
  <div id="timer">Kulunud aeg: 0.00 s</div>
</form>

<script>
const tableBody    = document.querySelector('#url-table tbody');
const timerDisplay = document.getElementById('timer');
let startTime      = null;
let timerInterval  = null;
let sessionTracker = null;
const inputs       = [];
const rows         = 13;

// URL-id ja juhendid
const urlTasks = [
  {
    url: "https://example.com/products/electronics/smartphones/iphone-13-pro-max?color=graphite&storage=256gb&utm_source=google&utm_medium=cpc&utm_campaign=summer_sale&region=europe&delivery=express&discount_code=SUMMER2023&referral=direct&user_preference=saved&currency=EUR&language=et&country=estonia",
    instruction: "Kustuta URL-ist kõik pärast 'smartphones/'",
    expected: "https://example.com/products/electronics/smartphones/"
  },
  {
    url: "https://online-store.com/shop/clothing/mens/shirts/casual/blue-striped-shirt-xl?ref=homepage&discount=15percent&tracking=email_promo&size=xl&material=cotton&brand=fashionista&in_stock=true&shipping=free&estimated_delivery=3days&gift_wrapping=available&reviews=4.5stars&recommended=true",
    instruction: "Kustuta URL-ist kõik pärast 'mens/'",
    expected: "https://online-store.com/shop/clothing/mens/"
  },
  {
    url: "https://university.edu/faculties/computer-science/courses/programming/java-advanced?semester=spring&year=2023&credits=6&professor=smith&classroom=b204&schedule=monday_wednesday&prerequisites=java_basics&textbook=required&enrollment=open&waitlist=available&syllabus=published&exam_date=20230515",
    instruction: "Kustuta URL-ist kõik pärast 'courses/'",
    expected: "https://university.edu/faculties/computer-science/courses/"
  },
  {
    url: "https://travel-agency.net/destinations/europe/italy/rome?duration=7days&accommodation=hotel&transport=flight&all-inclusive=true&departure_date=20230610&return_date=20230617&travelers=2adults&meal_plan=breakfast_included&tour_guide=english_speaking&attractions=colosseum_vatican_trevi&travel_insurance=recommended",
    instruction: "Kustuta URL-ist kõik pärast 'europe/'",
    expected: "https://travel-agency.net/destinations/europe/"
  },
  {
    url: "https://video-platform.com/channel/technology-reviews/videos/latest-gadgets-2023?autoplay=true&quality=hd&subtitles=en&duration=15minutes&uploaded=yesterday&views=10000&likes=2500&comments=350&creator=techexpert&membership=premium&notifications=enabled&playlist=tech_news&speed=normal&fullscreen=available",
    instruction: "Kustuta URL-ist kõik pärast 'videos/'",
    expected: "https://video-platform.com/channel/technology-reviews/videos/"
  },
  {
    url: "https://recipe-blog.org/categories/desserts/cakes/chocolate-cake-recipe?difficulty=easy&preparation-time=45min&servings=8&calories=350&ingredients=12&cuisine=international&dietary=contains_gluten&occasion=birthday&user_rating=4.8&comments=32&saved=5432&author=master_chef&published=20230301&updated=20230405&video_tutorial=included",
    instruction: "Kustuta URL-ist kõik pärast 'desserts/'",
    expected: "https://recipe-blog.org/categories/desserts/"
  },
  {
    url: "https://music-streaming.com/artists/rock/queen/albums/greatest-hits?shuffle=true&volume=80&repeat=all&release_year=1981&tracks=17&duration=58minutes&format=digital&remastered=true&label=emi&chart_position=1&sales=platinum&availability=worldwide&download=available&lyrics=included&related_artists=recommended&fan_favorites=highlighted",
    instruction: "Kustuta URL-ist kõik pärast 'albums/'",
    expected: "https://music-streaming.com/artists/rock/queen/albums/"
  },
  {
    url: "https://sports-news.info/football/leagues/premier-league/teams/manchester-united/players?position=forward&nationality=portuguese&age=38&jersey_number=7&goals=18&assists=3&matches_played=30&injuries=none&contract_until=2023&market_value=35million&previous_clubs=juventus_real_madrid&international_caps=196&captain=false&statistics=detailed",
    instruction: "Kustuta URL-ist kõik pärast 'teams/'",
    expected: "https://sports-news.info/football/leagues/premier-league/teams/"
  },
  {
    url: "https://bookstore.shop/genres/fiction/science-fiction/authors/isaac-asimov/foundation-series?format=hardcover&language=english&pages=512&publisher=bantam_books&isbn=9780553293357&publication_date=19510101&edition=special_anniversary&cover_art=classic&price=24.99&bestseller=true&reviews=4.7&in_stock=yes&delivery=worldwide&ebook_available=yes&audiobook=available",
    instruction: "Kustuta URL-ist kõik pärast 'authors/'",
    expected: "https://bookstore.shop/genres/fiction/science-fiction/authors/"
  },
  {
    url: "https://car-dealership.auto/inventory/new/electric/tesla/model-3?color=red&interior=white&autopilot=true&financing=available&range=358miles&battery=75kwh&acceleration=3.1seconds&top_speed=162mph&drive=all_wheel&warranty=4years&charging=supercharger_compatible&price=52990&tax_incentives=applicable&test_drive=available&delivery_time=2weeks",
    instruction: "Kustuta URL-ist kõik pärast 'electric/'",
    expected: "https://car-dealership.auto/inventory/new/electric/"
  },
  {
    url: "https://photography-forum.net/discussions/techniques/lighting/studio-lighting/soft-box-tutorial?level=beginner&comments=true&posted_by=professional_photographer&date=20230405&views=1243&replies=28&attachments=15&category=educational&featured=yes&equipment=godox_softbox&difficulty=medium&duration=45minutes&video_included=yes&downloadable_resources=lighting_diagrams&community_rating=4.9stars",
    instruction: "Kustuta URL-ist kõik pärast 'lighting/'",
    expected: "https://photography-forum.net/discussions/techniques/lighting/"
  },
  {
    url: "https://health-clinic.med/departments/cardiology/services/heart-screening?age=adult&insurance=accepted&appointment=required&doctor=smith&duration=60minutes&cost=150&preparation=fasting&results=24hours&follow_up=included&facility=main_hospital&parking=available&wheelchair_accessible=yes&languages=english_spanish&emergency_services=available&patient_portal=online_access",
    instruction: "Kustuta URL-ist kõik pärast 'services/'",
    expected: "https://health-clinic.med/departments/cardiology/services/"
  },
  {
    url: "https://smart-home-devices.tech/products/security/cameras/outdoor/night-vision-4k-camera?resolution=3840x2160&weather_resistant=ip67&connectivity=wifi_ethernet&storage=cloud_local&motion_detection=advanced&night_vision=50meters&field_of_view=130degrees&power=wired_battery&app_compatibility=ios_android&voice_control=alexa_google_siri&installation=diy_professional&warranty=3years&price=299.99",
    instruction: "Kustuta URL-ist kõik pärast 'cameras/'",
    expected: "https://smart-home-devices.tech/products/security/cameras/"
  },
  {
    url: "https://furniture-store.com/living-room/sofas/sectional/l-shaped/leather-brown-sectional?dimensions=10x12feet&material=genuine_leather&color=chestnut_brown&seats=5&reclining=yes&usb_ports=included&cup_holders=4&storage=underneath&assembly=required&delivery=white_glove&financing=0percent_24months&warranty=lifetime_frame&made_in=italy&in_stock=yes&showroom_display=available",
    instruction: "Kustuta URL-ist kõik pärast 'sofas/'",
    expected: "https://furniture-store.com/living-room/sofas/"
  },
  {
    url: "https://gardening-supplies.org/plants/outdoor/perennials/roses/hybrid-tea/red-passion?hardiness_zone=5_9&height=4feet&bloom_time=summer_fall&fragrance=strong&sun_exposure=full&water_needs=moderate&soil_type=well_drained&pruning=spring&disease_resistance=high&lifespan=25years&planting_season=spring&spacing=3feet&pollinator_friendly=yes&deer_resistant=no&container_suitable=yes",
    instruction: "Kustuta URL-ist kõik pärast 'perennials/'",
    expected: "https://gardening-supplies.org/plants/outdoor/perennials/"
  },
  {
    url: "https://fitness-equipment.store/cardio/treadmills/commercial-grade/marathon-trainer-9000?motor=4hp_continuous&max_speed=15mph&incline=0_15percent&deck_size=22x60inches&weight_capacity=400lbs&programs=42&heart_rate_monitor=wireless_handlebar&display=10inch_touchscreen&folding=yes&warranty=lifetime_frame_10year_motor&dimensions=82x35x57inches&weight=320lbs&assembly_required=partial&delivery=threshold",
    instruction: "Kustuta URL-ist kõik pärast 'treadmills/'",
    expected: "https://fitness-equipment.store/cardio/treadmills/"
  },
  {
    url: "https://pet-supplies.shop/dogs/food/dry/grain-free/salmon-sweet-potato?weight=30lbs&life_stage=adult&breed_size=all&protein_content=32percent&main_ingredient=wild_caught_salmon&supplements=omega3_glucosamine&artificial_ingredients=none&made_in=usa&subscription=available&veterinarian_developed=yes&allergen_free=chicken_wheat_corn&feeding_guidelines=included&shelf_life=18months&special_diet=sensitive_stomach",
    instruction: "Kustuta URL-ist kõik pärast 'food/'",
    expected: "https://pet-supplies.shop/dogs/food/"
  },
  {
    url: "https://camping-gear.outdoors/equipment/tents/4-season/expedition-extreme-shelter?capacity=3person&weight=8lbs&setup_time=5minutes&waterproof_rating=10000mm&wind_resistance=80mph&material=ripstop_nylon&poles=aluminum&vestibules=2&doors=2&ventilation=adjustable&packed_size=22x8inches&floor_dimensions=90x80inches&peak_height=45inches&color=high_visibility_orange&includes=footprint_guylines_repair_kit",
    instruction: "Kustuta URL-ist kõik pärast 'tents/'",
    expected: "https://camping-gear.outdoors/equipment/tents/"
  },
  {
    url: "https://art-supplies.creative/drawing/pencils/colored/professional-artist-grade/120-color-set?brand=prismacolor&lead_type=wax_based&lightfastness=high&blendability=superior&hardness=soft&packaging=tin_case&includes=color_chart_sharpener&layering_capability=excellent&water_resistant=yes&break_resistant=yes&pigment_concentration=high&suitable_for=detailed_work_shading_blending&paper_recommendation=medium_texture&skill_level=all_levels",
    instruction: "Kustuta URL-ist kõik pärast 'colored/'",
    expected: "https://art-supplies.creative/drawing/pencils/colored/"
  },
  {
    url: "https://coffee-enthusiast.beans/single-origin/ethiopia/yirgacheffe/washed-process/medium-roast?altitude=1800_2200meters&harvest=2023&processing=wet_processed&variety=heirloom&flavor_notes=floral_citrus_bergamot&body=medium&acidity=bright&roast_date=20230501&bag_size=12oz&grind=whole_bean&organic=certified&fair_trade=yes&direct_trade=yes&cupping_score=92&altitude_grown=high_mountain&farmer_cooperative=yirgacheffe_coffee_farmers_cooperative",
    instruction: "Kustuta URL-ist kõik pärast 'ethiopia/'",
    expected: "https://coffee-enthusiast.beans/single-origin/ethiopia/"
  },
  {
    url: "https://kitchen-appliances.home/cooking/mixers/stand/professional-series/7-quart-bowl-lift?power=1.3hp&speeds=10&attachments=whisk_paddle_dough_hook&bowl_material=stainless_steel&capacity=7quart&weight=25lbs&dimensions=14x14x16inches&warranty=5years&color=metallic_chrome&noise_level=quiet&planetary_mixing_action=59_touchpoints&bowl_handle=comfort_grip&splash_guard=included&special_features=slow_start_overload_protection&wattage=970&voltage=120",
    instruction: "Kustuta URL-ist kõik pärast 'stand/'",
    expected: "https://kitchen-appliances.home/cooking/mixers/stand/"
  },
  {
    url: "https://winter-sports.snow/skiing/downhill/all-mountain/skis/expert-level/carbon-fiber-construction?length=175cm&width=98mm&turn_radius=18m&core_material=wood_carbon&skill_level=advanced_expert&terrain=all_mountain&binding_included=marker_griffon&rocker_profile=tip_tail_rocker&flex=stiff&weight=1850g&edge_material=steel&base_material=sintered&warranty=2years&model_year=2023&technology=vibration_dampening&recommended_boot_size=24_5_29_5",
    instruction: "Kustuta URL-ist kõik pärast 'downhill/'",
    expected: "https://winter-sports.snow/skiing/downhill/"
  },
  {
    url: "https://musical-instruments.sound/guitars/electric/solid-body/stratocaster-style/professional-series-deluxe?body_wood=alder&neck_wood=maple&fretboard=rosewood&frets=22_jumbo&pickups=3_single_coil&controls=volume_2tone_5way_switch&bridge=tremolo&tuners=locking&finish=sunburst_gloss&scale_length=25_5inches&nut_width=1_65inches&radius=9_5inches&case_included=hardshell&country_of_origin=usa&weight=8lbs&electronics=active_passive_switchable",
    instruction: "Kustuta URL-ist kõik pärast 'electric/'",
    expected: "https://musical-instruments.sound/guitars/electric/"
  },
  {
    url: "https://home-theater.audio/speakers/surround/floor-standing/3-way/audiophile-reference?power_handling=300watts&frequency_response=28hz_25khz&sensitivity=92db&impedance=8ohms&drivers=1inch_tweeter_6inch_midrange_dual_10inch_woofers&crossover_frequency=350hz_3500hz&dimensions=48x14x16inches&weight=75lbs&finish=piano_black&bi_wiring_capability=yes&port_type=rear_firing&cabinet_material=mdf_reinforced&internal_bracing=extensive&magnetic_grille=detachable&spikes_included=adjustable",
    instruction: "Kustuta URL-ist kõik pärast 'surround/'",
    expected: "https://home-theater.audio/speakers/surround/"
  },
  {
    url: "https://organic-farm.fresh/produce/vegetables/leafy-greens/kale/dinosaur-variety?growing_method=organic&harvested=yesterday&packaging=bunch&weight=8oz&nutritional_info=high_vitamin_k_a_c&storage_recommendation=refrigerate_in_plastic_bag&shelf_life=5_7days&recipe_suggestions=included&farm_location=local_25miles&pesticide_free=yes&non_gmo=certified&seasonal_availability=year_round&price_per_pound=3_99&bulk_discount=available&csa_program=weekly_delivery&family_farm=third_generation",
    instruction: "Kustuta URL-ist kõik pärast 'vegetables/'",
    expected: "https://organic-farm.fresh/produce/vegetables/"
  },
  {
    url: "https://professional-tools.work/power-tools/drills/cordless/hammer-drill/brushless-motor-model?voltage=20v&battery_capacity=5ah&batteries_included=2&charger=fast_1hour&chuck_size=1_2inch&clutch_settings=22&speeds=3&torque=1200inlbs&weight=4_5lbs&led_light=3mode&case_included=heavy_duty&warranty=lifetime&dust_sealed=yes&vibration_control=advanced&bluetooth_connectivity=tool_tracking&impact_rate=32000bpm&no_load_speed=2000rpm&handle=ergonomic_overmold",
    instruction: "Kustuta URL-ist kõik pärast 'cordless/'",
    expected: "https://professional-tools.work/power-tools/drills/cordless/"
  },
  {
    url: "https://gaming-laptops.tech/high-performance/17-inch/rtx-series/i9-processor/ultra-gaming-pro?processor=intel_i9_12900hk&gpu=nvidia_rtx_3080ti_16gb&ram=64gb_ddr5&storage=2tb_nvme_ssd&display=17_3inch_4k_144hz&keyboard=per_key_rgb&cooling=liquid_metal_vapor_chamber&battery=99whr&weight=6_2lbs&dimensions=15_6x10_7x0_92inches&ports=thunderbolt4_usb3_2_hdmi2_1&wifi=6e&bluetooth=5_2&operating_system=windows_11_pro&warranty=2year_accidental_damage",
    instruction: "Kustuta URL-ist kõik pärast '17-inch/'",
    expected: "https://gaming-laptops.tech/high-performance/17-inch/"
  },
  {
    url: "https://digital-cameras.photo/mirrorless/full-frame/professional/high-resolution/landscape-specialized?megapixels=61&sensor_size=35mm&iso_range=100_51200&dynamic_range=15stops&autofocus_points=759&video_resolution=8k30p_4k120p&stabilization=5axis_8stops&weather_sealing=extensive&battery_life=500shots&continuous_shooting=10fps&storage=dual_cfexpress_type_b&evf_resolution=9_44million_dots&lcd=3_2inch_2100k_dots&connectivity=wifi6_bluetooth5_2_usbc&weight=675g&shutter_durability=500k_cycles",
    instruction: "Kustuta URL-ist kõik pärast 'full-frame/'",
    expected: "https://digital-cameras.photo/mirrorless/full-frame/"
  },
  {
    url: "https://hiking-equipment.trail/backpacks/multi-day/expedition/internal-frame/ultralight-design?capacity=65liters&weight=3_2lbs&material=dyneema_composite_fabric&back_system=adjustable_airflow&hip_belt=load_bearing_pockets&shoulder_straps=padded_load_lifters&hydration_compatible=3liter_reservoir&compartments=main_sleeping_bag_top_lid&external_attachments=trekking_poles_ice_axe&compression_straps=side_top&rain_cover=included&colors=forest_green_granite_gray&gender_specific=unisex&torso_length=adjustable_16_22inches&warranty=lifetime",
    instruction: "Kustuta URL-ist kõik pärast 'multi-day/'",
    expected: "https://hiking-equipment.trail/backpacks/multi-day/"
  },
  {
    url: "https://luxury-watches.time/swiss-made/automatic/chronograph/sapphire-crystal/exhibition-caseback?brand=omega&model=speedmaster&movement=co_axial_master_chronometer&power_reserve=60hours&case_material=stainless_steel&case_diameter=42mm&water_resistance=100meters&crystal=domed_sapphire_anti_reflective&dial_color=black&luminescence=super_luminova&bracelet=stainless_steel_adjustable&clasp=folding_safety&functions=chronograph_tachymeter_date&limited_edition=yes_5000pieces&warranty=5years_international&box_papers=included&retail_price=7500usd",
    instruction: "Kustuta URL-ist kõik pärast 'chronograph/'",
    expected: "https://luxury-watches.time/swiss-made/automatic/chronograph/"
  }
];

// Segame URL-id
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
  return array;
}

const shuffledTasks = shuffleArray([...urlTasks]);
const selectedTasks = shuffledTasks.slice(0, rows);

// Loome tabeli read
for (let i = 0; i < rows; i++) {
  const task = selectedTasks[i];

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${i + 1}</td>
    <td class="instruction-cell">${task.instruction}</td>
    <td><input type="text" value="${task.url}" data-expected="${task.expected}" class="incorrect" /></td>
  `;

  const input = tr.querySelector('input');
  input.addEventListener('input', handleInput);
  inputs.push(input);
  tableBody.appendChild(tr);
}

function handleInput() {
  if (startTime === null) {
    startTime = Date.now();
    timerInterval = setInterval(updateTimer, 50);
    // Start session tracking
    if (window.SessionTracker && window.RIIDAJA_USER) {
      sessionTracker = new SessionTracker(
        window.RIIDAJA_USER.email,
        window.RIIDAJA_USER.name,
        '<?php echo htmlspecialchars($_GET["task"] ?? "003"); ?>'
      );
      sessionTracker.start();
    }
  }

  let allCorrect = true;
  for (const input of inputs) {
    const expected = input.dataset.expected;

    if (input.value === expected) {
      input.classList.remove('incorrect');
      input.classList.add('correct');
    } else {
      input.classList.remove('correct');
      input.classList.add('incorrect');
      allCorrect = false;
    }
  }

  if (allCorrect) {
    clearInterval(timerInterval);
    // Mark session as complete (success)
    if (sessionTracker) sessionTracker.complete();
    const elapsed = (Date.now() - startTime) / 1000;
    timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
    fetch('save_result.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        elapsed: elapsed.toFixed(2),
        exercise_id: '<?php echo htmlspecialchars($_GET["task"] ?? "03"); ?>'
      })
    });
  }
}

function updateTimer() {
  const elapsed = (Date.now() - startTime) / 1000;
  timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
  if (elapsed >= 60) {
    clearInterval(timerInterval);
    // Mark session as complete (failed)
    if (sessionTracker) sessionTracker.complete();
    // Save failed attempt with negative elapsed time
    fetch('save_result.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        elapsed: -elapsed.toFixed(2),
        exercise_id: '<?php echo htmlspecialchars($_GET["task"] ?? "03"); ?>'
      })
    }).then(() => {
      alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
      location.reload();
    });
  }
}
</script>
