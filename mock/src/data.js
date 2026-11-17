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

// Footer pages. Edit `title` and `body` to set each page's content.
export const INFO_PAGES = {
  premium: { path: '/premium', label: 'Upgrade to Premium', title: 'Upgrade to Premium', body: 'Content coming soon.' },
  about: { path: '/about-us', label: 'About us', title: 'About us', body: 'Content coming soon.' },
  privacy: { path: '/privacy-policy', label: 'Privacy Policy', title: 'Privacy Policy', body: 'Content coming soon.' },
  contact: { path: '/contact-us', label: 'Contact us', title: 'Contact us', body: 'Content coming soon.' },
  testimonial: { path: '/testimonial', label: 'Testimonial', title: 'Testimonial', body: 'Content coming soon.' },
  terms: { path: '/terms-of-service', label: 'Terms of service', title: 'Terms of service', body: 'Content coming soon.' },
};

export const FOOTER_COLUMNS = [
  { title: 'Premium', links: [INFO_PAGES.premium] },
  { title: 'About', links: [INFO_PAGES.about, INFO_PAGES.privacy, INFO_PAGES.contact] },
  { title: 'Resources', links: [INFO_PAGES.testimonial, INFO_PAGES.terms] },
];
