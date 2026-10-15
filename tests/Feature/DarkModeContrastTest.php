<?php

namespace Tests\Feature;

use App\Enums\AgendaType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DarkModeContrastTest extends TestCase
{
    /**
     * Helper to compute relative luminance from hex color.
     */
    private function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Helper to compute WCAG contrast ratio between two hex colors.
     */
    private function contrastRatio(string $fg, string $bg): float
    {
        $l1 = $this->luminance($fg);
        $l2 = $this->luminance($bg);

        $lighter = max($l1, $l2);
        $darker  = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Blend an rgba color over a solid hex background to get effective hex.
     */
    private function blend(string $rgba, string $bgHex): string
    {
        if (!preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d\.]+))?\)/', $rgba, $m)) {
            return $rgba;
        }

        $fgR = (int) $m[1];
        $fgG = (int) $m[2];
        $fgB = (int) $m[3];
        $alpha = isset($m[4]) ? (float) $m[4] : 1.0;

        $bg = ltrim($bgHex, '#');
        $bgR = hexdec(substr($bg, 0, 2));
        $bgG = hexdec(substr($bg, 2, 2));
        $bgB = hexdec(substr($bg, 4, 2));

        $resR = (int) round(($fgR * $alpha) + ($bgR * (1 - $alpha)));
        $resG = (int) round(($fgG * $alpha) + ($bgG * (1 - $alpha)));
        $resB = (int) round(($fgB * $alpha) + ($bgB * (1 - $alpha)));

        return sprintf('#%02x%02x%02x', $resR, $resG, $resB);
    }

    /**
     * Centralized pairs of semantic tokens (text / background) across both light and dark modes.
     */
    public static function tokenPairsProvider(): array
    {
        return [
            ['Light', 'Heading on Base Surface', '#17365D', '#FFFFFF', 4.5, false],
            ['Light', 'Body Text on Base Surface', '#1F2937', '#FFFFFF', 4.5, false],
            ['Light', 'Soft Text on Base Surface', '#475569', '#FFFFFF', 4.5, false],
            ['Light', 'Muted Text on Surface Soft', '#64748B', '#F8FAFC', 4.5, false],
            ['Light', 'Link on Base Surface', '#007A84', '#FFFFFF', 4.5, false],
            ['Light', 'Brand Text on Base Surface', '#17365D', '#FFFFFF', 4.5, false],
            ['Light', 'On-Primary on Primary Button', '#FFFFFF', '#17365D', 4.5, false],
            ['Light', 'Table Head Text on Table Head BG', '#FFFFFF', '#17365D', 4.5, false],
            ['Light', 'Calendar Head Text on Calendar Head BG', '#FFFFFF', '#17365D', 4.5, false],
            ['Light', 'Sidebar Active Link on Nav Surface', '#FFFFFF', '#17365D', 4.5, false],
            ['Light', 'Success on Success-Soft', '#047857', '#ECFDF5', 4.5, false],
            ['Light', 'Danger on Danger-Soft', '#B91C1C', '#FEF2F2', 4.5, false],
            ['Light', 'Warning on Warning-Soft', '#B45309', '#FFFBEB', 4.5, false],

            ['Dark', 'Heading on Dark Surface', '#F8FAFC', '#181C28', 4.5, false],
            ['Dark', 'Body Text on Dark Surface', '#F1F5F9', '#181C28', 4.5, false],
            ['Dark', 'Soft Text on Dark Surface', '#CBD5E1', '#181C28', 4.5, false],
            ['Dark', 'Secondary Text on Dark Surface', '#94A3B8', '#181C28', 4.5, false],
            ['Dark', 'Muted Text on Dark Surface', '#94A3B8', '#181C28', 4.5, false],
            ['Dark', 'Brand/Navy on Dark Surface', '#93C5FD', '#181C28', 4.5, false],
            ['Dark', 'Link on Dark Surface', '#38BDF8', '#181C28', 4.5, false],
            ['Dark', 'On-Primary on Primary Button', '#FFFFFF', '#007A84', 4.5, false],
            ['Dark', 'Table Head Text on Table Head BG', '#F8FAFC', '#202536', 4.5, false],
            ['Dark', 'Calendar Head Text on Calendar Head BG', '#F8FAFC', '#202536', 4.5, false],
            ['Dark', 'Sidebar Active Link on Dark Accent BG', '#FFFFFF', '#007A84', 4.5, false],
            ['Dark', 'Success on Dark Surface', '#4ADE80', '#181C28', 4.5, false],
            ['Dark', 'Danger on Dark Surface', '#F87171', '#181C28', 4.5, false],
            ['Dark', 'Warning on Dark Surface', '#FBBF24', '#181C28', 4.5, false],
            ['Dark', 'Stat Primary on Dark Surface', '#38BDF8', '#181C28', 4.5, false],
        ];
    }

    #[DataProvider('tokenPairsProvider')]
    public function test_token_contrast_ratios_meet_wcag_standards(
        string $theme,
        string $name,
        string $fg,
        string $bg,
        float $minRatio,
        bool $isLarge
    ): void {
        $ratio = $this->contrastRatio($fg, $bg);
        $this->assertGreaterThanOrEqual(
            $minRatio,
            $ratio,
            sprintf("[%s] %s (%s on %s) contrast ratio %.2f:1 is below required %.2f:1", $theme, $name, $fg, $bg, $ratio, $minRatio)
        );
    }

    /**
     * Test all AgendaType enum chip colors in Light and Dark mode meet contrast standards.
     */
    public function test_agenda_type_chips_meet_contrast_requirements(): void
    {
        $darkBg = '#181C28';

        foreach (AgendaType::cases() as $type) {
            // Light mode: textColor() on color()
            $lightRatio = $this->contrastRatio($type->textColor(), $type->color());
            $this->assertGreaterThanOrEqual(
                4.5,
                $lightRatio,
                sprintf("AgendaType '%s' Light mode chip contrast ratio %.2f:1 is below 4.5:1", $type->value, $lightRatio)
            );

            // Dark mode: textColorDark() on blended colorDark() over dark surface
            $effectiveDarkBg = $this->blend($type->colorDark(), $darkBg);
            $darkRatio = $this->contrastRatio($type->textColorDark(), $effectiveDarkBg);
            $this->assertGreaterThanOrEqual(
                4.5,
                $darkRatio,
                sprintf("AgendaType '%s' Dark mode chip contrast ratio %.2f:1 is below 4.5:1", $type->value, $darkRatio)
            );
        }
    }

    /**
     * Scan views to ensure no unapproved hardcoded text colors remain on style attributes.
     */
    public function test_views_have_no_unapproved_hardcoded_text_colors(): void
    {
        $viewsPath = resource_path('views');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsPath));

        $allowedFileExceptions = [
            'reports/export_table.blade.php',
            'reports/export_pdf.blade.php',
            'reports/export_excel.blade.php',
            'reports/export_gdocs.blade.php',
            'reports/export_gsheets.blade.php',
            'welcome.blade.php',
        ];

        $violations = [];

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relPath = str_replace([$viewsPath . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $file->getPathname());

            if (in_array($relPath, $allowedFileExceptions, true)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // Look for color: #xxx or background: #xxx on style attributes
            if (preg_match_all('/style="[^"]*?(?:color|background(?:-color)?):\s*(#[0-9a-fA-F]{3,8})[^"]*?"/', $content, $matches)) {
                foreach ($matches[1] as $hex) {
                    // Allow pure white for logo container badge
                    if (in_array(strtolower($hex), ['#ffffff', '#fff'])) {
                        continue;
                    }
                    $violations[] = "{$relPath}: found hardcoded {$hex}";
                }
            }
        }

        $this->assertEmpty($violations, "Found unapproved hardcoded colors in views:\n" . implode("\n", array_slice($violations, 0, 10)));
    }

    /**
     * Ensure table headers across views do not use dark text styles over brand backgrounds.
     */
    public function test_table_headers_do_not_use_dark_text_over_brand_backgrounds(): void
    {
        $viewsPath = resource_path('views');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsPath));

        $allowedFileExceptions = [
            'reports/export_table.blade.php',
            'reports/export_pdf.blade.php',
            'reports/export_excel.blade.php',
            'reports/export_gdocs.blade.php',
            'reports/export_gsheets.blade.php',
            'welcome.blade.php',
        ];

        $violations = [];

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relPath = str_replace([$viewsPath . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $file->getPathname());
            if (in_array($relPath, $allowedFileExceptions, true)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // Check if <th has inline color style that is not approved light/inherit token
            if (preg_match_all('/<th[^>]*?style="[^"]*?color:\s*([^;"]+)[^"]*?"/i', $content, $matches)) {
                foreach ($matches[1] as $colorVal) {
                    $colorTrim = trim(strtolower($colorVal));
                    if (!in_array($colorTrim, ['var(--table-head-text)', 'var(--on-primary)', '#fff', '#ffffff', 'white', 'inherit'])) {
                        $violations[] = "{$relPath}: <th> has unapproved inline text color '{$colorVal}'";
                    }
                }
            }
        }

        $this->assertEmpty($violations, "Found unapproved <th> inline text colors in views:\n" . implode("\n", $violations));
    }
}

