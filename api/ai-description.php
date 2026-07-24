<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$title = trim($input['title'] ?? '');
$type = trim($input['type'] ?? '');
$city = trim($input['city'] ?? '');
$area = trim($input['area'] ?? '');
$bedrooms = trim($input['bedrooms'] ?? '');
$bathrooms = trim($input['bathrooms'] ?? '');
$amenities = $input['amenities'] ?? [];

$typeLabels = [
    'apartment' => 'Apartment', 'house' => 'House', 'room' => 'Room',
    'studio' => 'Studio', 'villa' => 'Villa'
];
$typeLabel = $typeLabels[$type] ?? ucfirst($type);

$amenityStr = !empty($amenities) ? implode(', ', $amenities) : 'standard amenities';
$bedStr = $bedrooms ? $bedrooms . ' bedroom' . ($bedrooms != 1 ? 's' : '') : '';
$bathStr = $bathrooms ? $bathrooms . ' bathroom' . ($bathrooms != 1 ? 's' : '') : '';
$roomStr = array_filter([$bedStr, $bathStr]);
$roomStr = !empty($roomStr) ? implode(' and ', $roomStr) : 'comfortable living spaces';
$areaStr = $area ? 'in ' . $area : '';
$cityStr = $city ? 'in ' . $city : '';

$descriptions = [];

$descriptions[] = "Welcome to this beautiful $typeLabel$areaStr$cityStr. Featuring $roomStr, this property offers a perfect blend of comfort and convenience. The space comes with $amenityStr, making it ideal for families and professionals alike. Located in a prime area with easy access to shopping, dining, and transport. Don't miss this opportunity to make it your new home.";

$descriptions[] = "Looking for the perfect place to live? This stunning $typeLabel$cityStr has everything you need. With $roomStr and $amenityStr, it provides a comfortable and modern living experience. The property is well-maintained and located$areaStr close to all essential facilities. A great choice for anyone seeking quality living in a vibrant neighborhood.";

$descriptions[] = "This gorgeous $typeLabel$areaStr$cityStr is a rare find. It boasts $roomStr along with $amenityStr. The property features spacious rooms, natural light, and a welcoming atmosphere. Whether you're a family, a working professional, or a student, this home offers the perfect balance of tranquility and accessibility. Schedule a visit today!";

$descriptions[] = "Presenting an exceptional $typeLabel$cityStr offering $roomStr and $amenityStr. This property stands out for its quality construction, thoughtful layout, and prime location$areaStr. Enjoy a lifestyle of comfort with nearby parks, schools, and commercial areas. A truly wonderful place to call home.";

$descriptions[] = "Discover your dream home with this remarkable $typeLabel$areaStr$cityStr. Offering $roomStr, the property is equipped with $amenityStr. Its strategic location ensures you're always connected to the best the city has to offer. Spacious, well-lit, and designed for modern living — this is where comfort meets convenience.";

echo json_encode(['descriptions' => $descriptions]);
