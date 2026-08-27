-- Global Invest Brasil | Banco PostgreSQL (Supabase)
-- Estrutura limpa para produtos, publicações técnicas, blog, contatos e administração.

CREATE TABLE IF NOT EXISTS admins (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'administrator' CHECK (role IN ('administrator','editor')),
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip_time ON login_attempts (ip_address, attempted_at);
CREATE INDEX IF NOT EXISTS idx_login_attempts_email_time ON login_attempts (email, attempted_at);

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','unsubscribed')),
  source VARCHAR(120),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_newsletter_status ON newsletter_subscribers (status);

CREATE TABLE IF NOT EXISTS cookie_consents (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  preferences VARCHAR(40) NOT NULL,
  consented_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(120) PRIMARY KEY,
  setting_value TEXT,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_categories (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  category_id BIGINT NULL REFERENCES product_categories(id) ON DELETE SET NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  summary TEXT,
  body TEXT,
  image_url VARCHAR(500),
  purchase_url VARCHAR(500),
  cta_label VARCHAR(100) NOT NULL DEFAULT 'Conheça agora',
  price DECIMAL(12,2),
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
  featured SMALLINT NOT NULL DEFAULT 0,
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_products_status_published ON products (status, published_at);
CREATE INDEX IF NOT EXISTS idx_products_category ON products (category_id);

CREATE TABLE IF NOT EXISTS publication_categories (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS publications (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  category_id BIGINT NULL REFERENCES publication_categories(id) ON DELETE SET NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  excerpt TEXT,
  content TEXT NOT NULL,
  image_url VARCHAR(500),
  image_alt VARCHAR(255),
  author_name VARCHAR(160) NOT NULL DEFAULT 'Global Invest Brasil',
  seo_title VARCHAR(255),
  seo_description VARCHAR(320),
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_publications_status_published ON publications (status, published_at);
CREATE INDEX IF NOT EXISTS idx_publications_category ON publications (category_id);

CREATE TABLE IF NOT EXISTS blog_categories (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  category_id BIGINT NULL REFERENCES blog_categories(id) ON DELETE SET NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  excerpt TEXT,
  content TEXT NOT NULL,
  image_url VARCHAR(500),
  image_alt VARCHAR(255),
  author_name VARCHAR(160) NOT NULL DEFAULT 'Global Invest Brasil',
  seo_title VARCHAR(255),
  seo_description VARCHAR(320),
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_blog_status_published ON blog_posts (status, published_at);
CREATE INDEX IF NOT EXISTS idx_blog_category ON blog_posts (category_id);

CREATE TABLE IF NOT EXISTS contacts (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  subject VARCHAR(220) NOT NULL,
  message TEXT NOT NULL,
  consent_at TIMESTAMP NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new' CHECK (status IN ('new','read','replied','archived')),
  internal_notes TEXT,
  replied_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_contacts_status_created ON contacts (status, created_at);
CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts (email);

INSERT INTO product_categories (name, slug, sort_order) VALUES
('Livros', 'livros', 10), ('E-books', 'ebooks', 20), ('Cursos e palestras', 'cursos-palestras', 30),
('Mentorias', 'mentorias', 40), ('Sites e e-commerces', 'sites-ecommerces', 50)
ON CONFLICT (slug) DO NOTHING;

INSERT INTO publication_categories (name, slug, sort_order) VALUES
('Gestão', 'gestao', 10), ('Investimentos', 'investimentos', 20), ('Negócios digitais', 'negocios-digitais', 30),
('Tecnologia', 'tecnologia', 40), ('Carreira', 'carreira', 50), ('Reflexões', 'reflexoes', 60)
ON CONFLICT (slug) DO NOTHING;

INSERT INTO blog_categories (name, slug, sort_order) VALUES
('Atualidades', 'atualidades', 10), ('Negócios', 'negocios', 20), ('Mercado', 'mercado', 30)
ON CONFLICT (slug) DO NOTHING;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Global Invest Brasil'), ('adsense_enabled', '0'), ('adsense_publisher_id', ''),
('google_site_verification', ''), ('contact_email', 'contato@globalinvestbr.com')
ON CONFLICT (setting_key) DO NOTHING;
