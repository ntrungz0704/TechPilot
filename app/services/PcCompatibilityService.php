<?php
/**
 * Service kiểm tra tính tương thích và ước tính công suất nguồn (PSU) của cấu hình PC
 */
class PcCompatibilityService
{
    private static function parseSpecs($specs): array {
        if (is_array($specs)) return $specs;
        if (is_string($specs)) return json_decode($specs, true) ?: [];
        return [];
    }

    /**
     * Flatten nested specs (schema_version 2) so compatibility fields are accessible at top level.
     */
    public static function flattenSpecs($specs): array {
        $parsed = self::parseSpecs($specs);
        $flat = $parsed;

        if (isset($parsed['attributes']) && is_array($parsed['attributes'])) {
            foreach ($parsed['attributes'] as $k => $v) {
                if (!isset($flat[$k])) {
                    $flat[$k] = $v;
                }
            }
        }

        if (isset($parsed['compatibility']) && is_array($parsed['compatibility'])) {
            foreach ($parsed['compatibility'] as $k => $v) {
                $flat[$k] = $v;
            }
        }

        // Aliases RAM
        if (isset($flat['ram_type']) && !isset($flat['memory_type'])) {
            $flat['memory_type'] = $flat['ram_type'];
        }
        if (isset($flat['max_ram_gb']) && !isset($flat['max_memory_gb'])) {
            $flat['max_memory_gb'] = $flat['max_ram_gb'];
        }
        if (isset($parsed['attributes']['ram_slots']) && !isset($flat['ram_slots'])) {
            $flat['ram_slots'] = $parsed['attributes']['ram_slots'];
        }
        if (isset($flat['cpu_generations']) && !isset($flat['bios_cpu_generations'])) {
            $flat['bios_cpu_generations'] = $flat['cpu_generations'];
        }

        // PSU Aliases
        if (!isset($flat['cpu_power_w']) || $flat['cpu_power_w'] === null) {
            $flat['cpu_power_w'] = $flat['max_power_w']
                ?? $flat['max_turbo_power_w']
                ?? $flat['power_draw_w']
                ?? $flat['tdp_w']
                ?? $flat['base_power_w']
                ?? null;
        }

        if (!isset($flat['gpu_power_w']) || $flat['gpu_power_w'] === null) {
            $flat['gpu_power_w'] = $flat['board_power_w']
                ?? $flat['power_draw_w']
                ?? $flat['power_w']
                ?? $flat['tgp_w']
                ?? $flat['tbp_w']
                ?? null;
        }

        if (!isset($flat['gpu_rec_psu_w'])) {
            $flat['gpu_rec_psu_w'] = $flat['minimum_system_psu_w']
                ?? $flat['recommended_psu_w']
                ?? null;
        }

        if (!isset($flat['ram_module_count'])) {
            $flat['ram_module_count'] = $flat['module_count']
                ?? $flat['modules']
                ?? 1;
        }

        if (!isset($flat['psu_wattage_w'])) {
            $flat['psu_wattage_w'] = $flat['wattage_w']
                ?? $flat['rated_power_w']
                ?? $flat['capacity_w']
                ?? $flat['psu_wattage']
                ?? null;
        }

        if (!isset($flat['case_form_factors'])) {
            $flat['case_form_factors'] = $flat['supported_form_factors']
                ?? $flat['supported_motherboard_form_factors']
                ?? [];
        }

        if (!isset($flat['cooling_type_name'])) {
            $flat['cooling_type_name'] = $flat['cooling_type']
                ?? $flat['cooler_type']
                ?? 'air';
        }

        return $flat;
    }

    /**
     * Kiểm tra xem CPU có nhân đồ họa tích hợp (iGPU) hay không
     */
    public static function hasIntegratedGraphics(string $cpuName, array $cpuSpecs): bool {
        if (isset($cpuSpecs['integrated_graphics'])) {
            return (bool)$cpuSpecs['integrated_graphics'];
        }
        if (isset($cpuSpecs['igpu'])) {
            return (bool)$cpuSpecs['igpu'];
        }
        $nameLower = strtolower($cpuName);
        if (preg_match('/\b\d{4,5}[k]?f\b/i', $nameLower) || str_contains($nameLower, '-f')) {
            return false;
        }
        if (str_contains($nameLower, '7500f')) {
            return false;
        }
        if (str_contains($nameLower, 'ryzen') && str_contains($nameLower, 'g')) {
            return true;
        }
        if (str_contains($nameLower, 'intel') || str_contains($nameLower, 'core')) {
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra xem CPU có kèm tản nhiệt stock đi kèm hay không
     */
    public static function hasStockCooler(string $cpuName, array $cpuSpecs): bool {
        if (isset($cpuSpecs['has_stock_cooler'])) {
            return (bool)$cpuSpecs['has_stock_cooler'];
        }
        if (isset($cpuSpecs['stock_cooler'])) {
            return (bool)$cpuSpecs['stock_cooler'];
        }
        $nameLower = strtolower($cpuName);
        if (preg_match('/[kfx]\b/i', $nameLower) || str_contains($nameLower, 'ultra') || str_contains($nameLower, 'threadripper')) {
            return false;
        }
        return true;
    }

    public static function roundUpToPsuStep(float $wattage): float
    {
        $steps = [450, 500, 550, 600, 650, 700, 750, 800, 850, 900, 1000, 1200, 1300, 1500, 1600];
        foreach ($steps as $s) {
            if ($s >= $wattage) {
                return (float)$s;
            }
        }
        return ceil($wattage / 50) * 50;
    }

    public static function calculatePowerRequirements(array $build): array
    {
        $cpu = $build['cpu'] ?? null;
        $gpu = $build['vga'] ?? null;
        $mainboard = $build['mainboard'] ?? null;
        $ram = $build['ram'] ?? null;
        $cooler = $build['cooler'] ?? null;
        $case = $build['case'] ?? null;
        $psu = $build['psu'] ?? null;

        $storages = [];
        if (isset($build['storage'])) {
            $storages[] = $build['storage'];
        }
        if (isset($build['storages']) && is_array($build['storages'])) {
            $storages = array_merge($storages, $build['storages']);
        }

        $fans = [];
        if (isset($build['fan'])) {
            $fans[] = $build['fan'];
        }
        if (isset($build['fans']) && is_array($build['fans'])) {
            $fans = array_merge($fans, $build['fans']);
        }

        $missingPowerFields = [];

        // 1. CPU Peak Power
        $cpuSpecs = $cpu ? self::flattenSpecs($cpu['specs'] ?? '') : [];
        $cpuPeak = 0;
        if ($cpu) {
            if ($cpuSpecs['cpu_power_w'] !== null) {
                $cpuPeak = (float)$cpuSpecs['cpu_power_w'];
            } else {
                $missingPowerFields[] = 'Bộ vi xử lý (CPU)';
            }
        }

        // 2. GPU Load Power
        $gpuSpecs = $gpu ? self::flattenSpecs($gpu['specs'] ?? '') : [];
        $gpuLoad = 0;
        if ($gpu) {
            if ($gpuSpecs['gpu_power_w'] !== null) {
                $gpuLoad = (float)$gpuSpecs['gpu_power_w'];
            } else {
                $missingPowerFields[] = 'Card màn hình (VGA)';
            }
        }

        // 3. Motherboard Power
        $mbPower = 0;
        if ($mainboard) {
            $mbSpecs = self::flattenSpecs($mainboard['specs'] ?? '');
            $mbChipset = strtoupper($mbSpecs['chipset'] ?? '');
            $isHighEndMb = (str_starts_with($mbChipset, 'Z') || str_starts_with($mbChipset, 'X'));
            $mbPower = $isHighEndMb ? 50 : 30;
        }

        // 4. RAM Power
        $ramPower = 0;
        if ($ram) {
            $ramSpecs = self::flattenSpecs($ram['specs'] ?? '');
            $ramModulesCount = (int)($ramSpecs['ram_module_count'] ?? 1);
            $ramPower = $ramModulesCount * 5;
        }

        // 5. SSD & HDD Power
        $storagePower = count($storages) * 5;

        // 6. Cooler & Fan Power
        $coolerPower = $cooler ? 10 : 0;
        $fanPower = ($case || !empty($fans)) ? 15 : 0;
        $usbMisc = ($cpu || $mainboard) ? 15 : 0;

        $estimatedPeak = $cpuPeak + $gpuLoad + $mbPower + $ramPower + $storagePower + $coolerPower + $fanPower + $usbMisc;

        $targetWithHeadroom = $estimatedPeak * 1.30;
        $gpuRecommended = $gpu ? (float)($gpuSpecs['gpu_rec_psu_w'] ?? 0) : 0;

        $recommendedRaw = max($targetWithHeadroom, $gpuRecommended);
        $recommendedPsu = ($estimatedPeak > 0 || $gpuRecommended > 0) ? self::roundUpToPsuStep($recommendedRaw) : 0;

        $psuSpecs = $psu ? self::flattenSpecs($psu['specs'] ?? '') : [];
        $selectedPsuW = $psu ? (float)($psuSpecs['psu_wattage_w'] ?? 0) : 0;

        $headroomW = ($selectedPsuW > 0 && $estimatedPeak > 0) ? ($selectedPsuW - $estimatedPeak) : 0;
        $headroomPercent = ($selectedPsuW > 0 && $estimatedPeak > 0) ? round(($headroomW / $estimatedPeak) * 100, 1) : 0;
        $isSufficient = ($selectedPsuW > 0 && $recommendedPsu > 0) ? ($selectedPsuW >= $recommendedPsu) : null;

        $dataQuality = 'exact';
        if (!empty($missingPowerFields)) {
            $dataQuality = 'insufficient';
        } elseif (!$cpu && !$gpu) {
            $dataQuality = 'estimated';
        }

        return [
            'estimated_peak_w'          => round($estimatedPeak, 1),
            'recommended_psu_w'          => $recommendedPsu,
            'gpu_minimum_psu_w'          => $gpuRecommended,
            'selected_psu_w'             => $selectedPsuW,
            'headroom_w'                 => round($headroomW, 1),
            'headroom_percent'           => $headroomPercent,
            'is_selected_psu_sufficient' => $isSufficient,
            'data_quality'               => $dataQuality,
            'missing_power_fields'       => $missingPowerFields,
            'details'                    => [
                'CPU Peak'               => $cpuPeak . 'W',
                'GPU Load'               => $gpuLoad . 'W',
                'Bo mạch chủ'            => $mbPower . 'W',
                'Bộ nhớ RAM'             => $ramPower . 'W',
                'Ổ cứng lưu trữ'         => $storagePower . 'W',
                'Tản nhiệt'              => $coolerPower . 'W',
                'Quạt thùng máy'         => $fanPower . 'W',
                'Thiết bị ngoại vi / USB' => $usbMisc . 'W'
            ]
        ];
    }

    public static function checkCompatibility(array $build, array $candidate, string $candidateType): array
    {
        $blockers = [];
        $warnings = [];

        $cpu = $build['cpu'] ?? null;
        $mainboard = $build['mainboard'] ?? null;
        $ram = $build['ram'] ?? null;
        $gpu = $build['vga'] ?? null;
        $cooler = $build['cooler'] ?? null;
        $case = $build['case'] ?? null;

        $candidateSpecs = self::flattenSpecs($candidate['specs'] ?? '');

        // 1. Kiểm tra khi ứng cử viên là CPU
        if ($candidateType === 'cpu') {
            $cpuSpecs = $candidateSpecs;
            $cpuSocket = $cpuSpecs['socket'] ?? '';
            $cpuGen = $cpuSpecs['generation'] ?? '';
            $hasIgpu = self::hasIntegratedGraphics($candidate['name'] ?? '', $cpuSpecs);

            if (!$hasIgpu && !$gpu) {
                $warnings[] = "CPU này không tích hợp nhân đồ họa iGPU. Bắt buộc phải chọn thêm Card màn hình rời (VGA) để xuất hình.";
            }

            if ($mainboard) {
                $mbSpecs = self::flattenSpecs($mainboard['specs'] ?? '');
                $mbSocket = $mbSpecs['socket'] ?? '';
                $mbSupportedGens = $mbSpecs['bios_cpu_generations'] ?? [];
                $mbBiosGens = $mbSpecs['bios_warning_generations'] ?? [];

                if ($cpuSocket !== '' && $mbSocket !== '' && strcasecmp($cpuSocket, $mbSocket) !== 0) {
                    $blockers[] = "Socket CPU ({$cpuSocket}) không khớp với Socket của Bo mạch chủ ({$mbSocket}).";
                }

                if ($cpuGen !== '' && !empty($mbSupportedGens) && !in_array($cpuGen, $mbSupportedGens)) {
                    $blockers[] = "Bo mạch chủ không hỗ trợ dòng CPU thế hệ {$cpuGen} này.";
                }

                if ($cpuGen !== '' && !empty($mbBiosGens) && in_array($cpuGen, $mbBiosGens)) {
                    $warnings[] = "CPU thế hệ {$cpuGen} có thể cần cập nhật BIOS cho Bo mạch chủ {$mbSpecs['chipset']} trước khi lắp đặt.";
                }
            }
        }

        // 2. Kiểm tra khi ứng cử viên là Mainboard
        if ($candidateType === 'mainboard') {
            $mbSpecs = $candidateSpecs;
            $mbSocket = $mbSpecs['socket'] ?? '';

            if ($cpu) {
                $cpuSpecs = self::flattenSpecs($cpu['specs'] ?? '');
                $cpuSocket = $cpuSpecs['socket'] ?? '';
                if ($cpuSocket !== '' && $mbSocket !== '' && strcasecmp($cpuSocket, $mbSocket) !== 0) {
                    $blockers[] = "Socket Bo mạch chủ ({$mbSocket}) không khớp với Socket của CPU đang chọn ({$cpuSocket}).";
                }
            }

            if ($case) {
                $caseSpecs = self::flattenSpecs($case['specs'] ?? '');
                $caseFormFactors = $caseSpecs['case_form_factors'] ?? [];
                $mbForm = $mbSpecs['form_factor'] ?? '';

                if ($mbForm !== '' && !empty($caseFormFactors) && !in_array($mbForm, $caseFormFactors)) {
                    $blockers[] = "Kích thước Bo mạch chủ ({$mbForm}) quá lớn, không vừa với thùng máy (Case chỉ hỗ trợ: " . implode(', ', $caseFormFactors) . ").";
                }
            }
        }

        // 3. Kiểm tra khi ứng cử viên là RAM
        if ($candidateType === 'ram') {
            $ramSpecs = $candidateSpecs;
            $ramType = $ramSpecs['memory_type'] ?? '';
            $ramCapacity = (int)($ramSpecs['capacity_gb'] ?? 0);
            $ramModules = (int)($ramSpecs['ram_module_count'] ?? 1);

            if ($mainboard) {
                $mbSpecs = self::flattenSpecs($mainboard['specs'] ?? '');
                $mbRamType = $mbSpecs['memory_type'] ?? '';
                $mbSlots = (int)($mbSpecs['ram_slots'] ?? 4);
                $mbMaxMem = (int)($mbSpecs['max_memory_gb'] ?? 128);

                if ($mbRamType !== '' && $ramType !== '' && strcasecmp($mbRamType, $ramType) !== 0) {
                    $blockers[] = "RAM chuẩn {$ramType} không khớp với khe cắm RAM chuẩn {$mbRamType} trên Bo mạch chủ.";
                }

                if ($ramModules > $mbSlots) {
                    $blockers[] = "Số lượng thanh RAM ({$ramModules} thanh) vượt quá số khe cắm RAM trên Bo mạch chủ ({$mbSlots} khe).";
                }

                if ($ramCapacity > $mbMaxMem) {
                    $blockers[] = "Tổng dung lượng bộ nhớ RAM ({$ramCapacity}GB) vượt quá mức dung lượng tối đa mà Bo mạch chủ hỗ trợ ({$mbMaxMem}GB).";
                }
            }
        }

        // 4. Kiểm tra khi ứng cử viên là GPU
        if ($candidateType === 'vga' || $candidateType === 'gpu') {
            $gpuSpecs = $candidateSpecs;
            $gpuLength = (float)($gpuSpecs['length_mm'] ?? 0);

            if ($case) {
                $caseSpecs = self::flattenSpecs($case['specs'] ?? '');
                $caseMaxGpu = (float)($caseSpecs['max_gpu_length_mm'] ?? 300);

                if ($gpuLength > 0 && $caseMaxGpu > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Card màn hình dài {$gpuLength}mm, vượt quá giới hạn chiều dài VGA tối đa của thùng máy ({$caseMaxGpu}mm).";
                }
            }
        }

        // 5. Kiểm tra khi ứng cử viên là Nguồn máy tính (PSU)
        if ($candidateType === 'psu') {
            $psuSpecs = $candidateSpecs;
            $psuWattage = (float)($psuSpecs['psu_wattage_w'] ?? 0);

            $powerReq = self::calculatePowerRequirements($build);
            $recommendedWattage = $powerReq['recommended_psu_w'];
            $estimatedPeak = $powerReq['estimated_peak_w'];

            if ($psuWattage > 0 && $recommendedWattage > 0 && $psuWattage < $recommendedWattage) {
                $blockers[] = "Công suất nguồn ({$psuWattage}W) thấp hơn mức nguồn tối thiểu khuyến nghị cho cấu hình này ({$recommendedWattage}W).";
            } elseif ($psuWattage > 0 && $estimatedPeak > 0 && ($psuWattage - $estimatedPeak) < ($estimatedPeak * 0.15)) {
                $warnings[] = "Công suất nguồn ({$psuWattage}W) vừa đủ nhưng mức dự phòng công suất dưới 15%.";
            }
        }

        // 6. Kiểm tra khi ứng cử viên là Thùng máy (Case)
        if ($candidateType === 'case') {
            $caseSpecs = $candidateSpecs;
            $caseFormFactors = $caseSpecs['case_form_factors'] ?? [];
            $caseMaxGpu = (float)($caseSpecs['max_gpu_length_mm'] ?? 0);
            $caseMaxCooler = (float)($caseSpecs['max_cpu_cooler_height_mm'] ?? 0);

            if ($mainboard) {
                $mbSpecs = self::flattenSpecs($mainboard['specs'] ?? '');
                $mbForm = $mbSpecs['form_factor'] ?? '';

                if ($mbForm !== '' && !empty($caseFormFactors) && !in_array($mbForm, $caseFormFactors)) {
                    $blockers[] = "Thùng máy không hỗ trợ kích thước Bo mạch chủ đang chọn ({$mbForm}).";
                }
            }

            if ($gpu) {
                $gpuSpecs = self::flattenSpecs($gpu['specs'] ?? '');
                $gpuLength = (float)($gpuSpecs['length_mm'] ?? 0);

                if ($gpuLength > 0 && $caseMaxGpu > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Thùng máy giới hạn VGA tối đa {$caseMaxGpu}mm, không vừa với Card màn hình đang chọn ({$gpuLength}mm).";
                }
            }

            if ($cooler) {
                $coolerSpecs = self::flattenSpecs($cooler['specs'] ?? '');
                $coolerHeight = (float)($coolerSpecs['height_mm'] ?? 0);
                $coolerType = $coolerSpecs['cooling_type_name'] ?? 'air';

                if ($coolerType === 'air' && $coolerHeight > 0 && $caseMaxCooler > 0 && $coolerHeight > $caseMaxCooler) {
                    $blockers[] = "Thùng máy giới hạn chiều cao tản nhiệt khí tối đa {$caseMaxCooler}mm, không vừa với tản nhiệt khí đang chọn ({$coolerHeight}mm).";
                }
            }
        }

        // 7. Kiểm tra khi ứng cử viên là Tản nhiệt PC (Cooler)
        if ($candidateType === 'cooler') {
            $coolerSpecs = $candidateSpecs;
            $coolerHeight = (float)($coolerSpecs['height_mm'] ?? 0);
            $coolerSockets = $coolerSpecs['supported_sockets'] ?? [];
            $coolerType = $coolerSpecs['cooling_type_name'] ?? 'air';

            if ($cpu) {
                $cpuSpecs = self::flattenSpecs($cpu['specs'] ?? '');
                $cpuSocket = $cpuSpecs['socket'] ?? '';

                if ($cpuSocket !== '' && !empty($coolerSockets) && !in_array($cpuSocket, $coolerSockets)) {
                    $blockers[] = "Tản nhiệt không hỗ trợ chân socket ({$cpuSocket}) của CPU đang chọn.";
                }
            }

            if ($case && $coolerType === 'air') {
                $caseSpecs = self::flattenSpecs($case['specs'] ?? '');
                $caseMaxCooler = (float)($caseSpecs['max_cpu_cooler_height_mm'] ?? 0);

                if ($coolerHeight > 0 && $caseMaxCooler > 0 && $coolerHeight > $caseMaxCooler) {
                    $blockers[] = "Tản nhiệt khí cao {$coolerHeight}mm, vượt quá giới hạn chiều cao tối đa của thùng máy ({$caseMaxCooler}mm).";
                }
            }
        }

        return [
            'compatible' => empty($blockers),
            'blockers' => $blockers,
            'warnings' => $warnings
        ];
    }
}
