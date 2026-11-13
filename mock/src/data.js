// Mock content for the homepage. Text mirrors the public homepage; images are placeholders.

// Region codes as used on the live site (note: Manitoba is "MA" there, not the postal "MB").
export const PROVINCES = [
  { code: 'AB', name: 'Alberta' },
  { code: 'BC', name: 'British Columbia' },
  { code: 'MA', name: 'Manitoba' },
  { code: 'NB', name: 'New Brunswick' },
  { code: 'NL', name: 'Newfoundland & Labrador' },
  { code: 'NT', name: 'Northwest Territories' },
  { code: 'NS', name: 'Nova Scotia' },
  { code: 'NU', name: 'Nunavut' },
  { code: 'ON', name: 'Ontario' },
  { code: 'PE', name: 'Prince Edward Island' },
  { code: 'QC', name: 'Quebec' },
  { code: 'SK', name: 'Saskatchewan' },
  { code: 'YT', name: 'Yukon' },
];

export function findProvince(code = '') {
  return PROVINCES.find((p) => p.code === code.toUpperCase());
}

export function practicePath(code) {
  return `/practice/${code}`;
}

export const STEPS = [
  {
    title: 'Do the Practice Tests',
    text: 'Access to our test questions database is free. So, make sure you do as many of them as you can.',
    image: 'Illustration: studying the driver’s guide',
  },
  {
    title: 'Upgrade to Premium',
    text: 'Unlock unlimited access to all practice tests, and advanced resources to ensure you pass with flying colors.',
    image: 'Illustration: advanced practice test',
  },
  {
    title: 'Pass Your Real Test',
    text: 'Ace your official exam with confidence and successfully get your driver’s license on your first try.',
    image: 'Illustration: new driver with license',
  },
];

export const FOOTER_COLUMNS = [
  { title: 'Premium', links: ['Upgrade to Premium'] },
  { title: 'About', links: ['About us', 'Privacy Policy', 'Contact us'] },
  { title: 'Resources', links: ['Testimonial', 'Terms of service'] },
];
