<?php
/**
 * Service kiểm tra tính tương thích và ước tính công suất nguồn (PSU) của cấu hình PC
 */
class PcCompatibilityService
{
    /**
     * Ước tính công suất nguồn cần thiết cho cấu hình
     * Trả về mảng chứa: estimated_peak_w, recommended_psu_w, reasons
     */
    public static function calculatePowerRequirements(array $build): array
    {
        $cpu = $build['cpu'] ?? null;
        $gpu = $build['gpu'] ?? null;
        $mainboard = $build['mainboard'] ?? null;
        $ram = $build['ram'] ?? null;
        $cooler = $build['cooler'] ?? null;
        $case = $build['case'] ?? null;
        
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

        // 1. CPU Peak Power
        $cpuSpecs = $cpu ? (json_decode($cpu['specs'] ?? '', true) ?: []) : [];
        $cpuPeak = 65; // default fallback
        if (isset($cpuSpecs['max_turbo_power_w'])) {
            $cpuPeak = (float)$cpuSpecs['max_turbo_power_w'];
        } elseif (isset($cpuSpecs['ppt_w'])) {
            $cpuPeak = (float)$cpuSpecs['ppt_w'];
        } elseif (isset($cpuSpecs['tdp_w'])) {
            $cpuPeak = (float)$cpuSpecs['tdp_w'];
        }

        // 2. GPU Load Power
        $gpuSpecs = $gpu ? (json_decode($gpu['specs'] ?? '', true) ?: []) : [];
        $gpuLoad = $gpu ? (float)($gpuSpecs['power_w'] ?? 150) : 0;

        // 3. Motherboard Power (50W với phổ thông, 70W với high-end)
        $mbSpecs = $mainboard ? (json_decode($mainboard['specs'] ?? '', true) ?: []) : [];
        $mbChipset = strtoupper($mbSpecs['chipset'] ?? '');
        $isHighEndMb = (strpos($mbChipset, 'Z') === 0 || strpos($mbChipset, 'X') === 0);
        $mbPower = $isHighEndMb ? 70 : 50;

        // 4. RAM Power
        $ramSpecs = $ram ? (json_decode($ram['specs'] ?? '', true) ?: []) : [];
        $ramPowerPerModule = (float)($ramSpecs['power_w_per_module'] ?? 4);
        $ramModulesCount = (int)($ramSpecs['modules'] ?? 2);
        $ramPower = $ram ? ($ramModulesCount * $ramPowerPerModule) : 0;

        // 5. SSD & HDD Power
        $storagePower = 0;
        foreach ($storages as $st) {
            $stSpecs = json_decode($st['specs'] ?? '', true) ?: [];
            $storagePower += (float)($stSpecs['power_w'] ?? 6);
        }

        // 6. Cooler Power (fan + pump)
        $coolerSpecs = $cooler ? (json_decode($cooler['specs'] ?? '', true) ?: []) : [];
        $coolerFanCount = (int)($coolerSpecs['fan_count'] ?? 1);
        $coolerFanPower = (float)($coolerSpecs['fan_power_w'] ?? 3);
        $coolerPumpPower = (float)($coolerSpecs['pump_power_w'] ?? 0);
        $coolerPower = $cooler ? ($coolerPumpPower + ($coolerFanCount * $coolerFanPower)) : 0;

        // 7. Case Fans Power
        $fanPower = 0;
        foreach ($fans as $fn) {
            $fnSpecs = json_decode($fn['specs'] ?? '', true) ?: [];
            $fanPower += (float)($fnSpecs['power_w'] ?? 3);
        }
        if (empty($fans) && $case) {
            // Tự động cộng thêm 3 quạt case mặc định (3 x 3W = 9W)
            $fanPower = 9;
        }

        // 8. USB / RGB dự phòng
        $usbMisc = 20;

        // Tổng công suất tải đỉnh ước tính (Estimated Peak Wattage)
        $estimatedPeak = $cpuPeak + $gpuLoad + $mbPower + $ramPower + $storagePower + $coolerPower + $fanPower + $usbMisc;

        // Tính công suất nguồn khuyến nghị với 30% Headroom, làm tròn lên bậc 50W
        $headroomPsu = ceil(($estimatedPeak * 1.30) / 50) * 50;

        // Mức đề xuất từ hãng GPU (Manufacturer Recommended PSU) làm mức sàn
        $gpuRecommended = $gpu ? (float)($gpuSpecs['recommended_psu_w'] ?? 550) : 300;

        $recommendedPsu = max($headroomPsu, $gpuRecommended);

        return [
            'estimated_peak_w' => $estimatedPeak,
            'recommended_psu_w' => $recommendedPsu,
            'cpu_peak_w' => $cpuPeak,
            'gpu_load_w' => $gpuLoad,
            'details' => [
                'CPU Peak' => $cpuPeak . 'W',
                'GPU Load' => $gpuLoad . 'W',
                'Bo mạch chủ' => $mbPower . 'W',
                'Bộ nhớ RAM' => $ramPower . 'W',
                'Ổ cứng lưu trữ' => $storagePower . 'W',
                'Tản nhiệt' => $coolerPower . 'W',
                'Quạt thùng máy' => $fanPower . 'W',
                'Thiết bị ngoại vi / USB' => $usbMisc . 'W'
            ]
        ];
    }

    /**
     * Kiểm tra tính tương thích giữa linh kiện ứng cử viên (candidate) và cấu hình hiện tại
     * Trả về mảng chứa: compatible (bool), blockers (array), warnings (array)
     */
    public static function checkCompatibility(array $build, array $candidate, string $candidateType): array
    {
        $blockers = [];
        $warnings = [];

        $cpu = $build['cpu'] ?? null;
        $mainboard = $build['mainboard'] ?? null;
        $ram = $build['ram'] ?? null;
        $gpu = $build['gpu'] ?? null;
        $cooler = $build['cooler'] ?? null;
        $case = $build['case'] ?? null;
        $psu = $build['psu'] ?? null;

        $candidateSpecs = json_decode($candidate['specs'] ?? '', true) ?: [];

        // 1. Kiểm tra khi ứng cử viên là CPU
        if ($candidateType === 'cpu') {
            $cpuSpecs = $candidateSpecs;
            $cpuSocket = $cpuSpecs['socket'] ?? '';
            $cpuGen = (int)($cpuSpecs['generation'] ?? 0);
            $cpuBrand = strtolower($cpuSpecs['brand_platform'] ?? '');

            // So khớp với Mainboard đã chọn
            if ($mainboard) {
                $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
                $mbSocket = $mbSpecs['socket'] ?? '';
                $mbSupportedGens = $mbSpecs['supported_cpu_generations'] ?? [];
                $mbBiosGens = $mbSpecs['bios_update_for_generations'] ?? [];

                if ($cpuSocket !== '' && $mbSocket !== '' && strcasecmp($cpuSocket, $mbSocket) !== 0) {
                    $blockers[] = "Socket CPU ({$cpuSocket}) không khớp với Socket của Bo mạch chủ ({$mbSocket}).";
                }

                if ($cpuGen > 0 && !empty($mbSupportedGens) && !in_array($cpuGen, $mbSupportedGens)) {
                    $blockers[] = "Bo mạch chủ không hỗ trợ dòng CPU thế hệ thứ {$cpuGen} này.";
                }

                if ($cpuGen > 0 && !empty($mbBiosGens) && in_array($cpuGen, $mbBiosGens)) {
                    $warnings[] = "CPU thế hệ thứ {$cpuGen} cần cập nhật BIOS cho Bo mạch chủ {$mbSpecs['chipset']} trước khi lắp đặt.";
                }
            }
        }

        // 2. Kiểm tra khi ứng cử viên là Mainboard
        if ($candidateType === 'mainboard') {
            $mbSpecs = $candidateSpecs;
            $mbSocket = $mbSpecs['socket'] ?? '';
            $mbRamType = $mbSpecs['memory_type'] ?? '';
            $mbSupportedGens = $mbSpecs['supported_cpu_generations'] ?? [];
            $mbBiosGens = $mbSpecs['bios_update_for_generations'] ?? [];

            // So khớp với CPU đã chọn
            if ($cpu) {
                $cpuSpecs = json_decode($cpu['specs'] ?? '', true) ?: [];
                $cpuSocket = $cpuSpecs['socket'] ?? '';
                $cpuGen = (int)($cpuSpecs['generation'] ?? 0);

                if ($mbSocket !== '' && $cpuSocket !== '' && strcasecmp($mbSocket, $cpuSocket) !== 0) {
                    $blockers[] = "Socket Bo mạch chủ ({$mbSocket}) không khớp với Socket của CPU ({$cpuSocket}).";
                }

                if ($cpuGen > 0 && !empty($mbSupportedGens) && !in_array($cpuGen, $mbSupportedGens)) {
                    $blockers[] = "Bo mạch chủ {$mbSpecs['chipset']} không hỗ trợ dòng CPU thế hệ thứ {$cpuGen} đang chọn.";
                }

                if ($cpuGen > 0 && !empty($mbBiosGens) && in_array($cpuGen, $mbBiosGens)) {
                    $warnings[] = "Bo mạch chủ này cần được cập nhật bản BIOS mới nhất để nhận diện CPU thế hệ {$cpuGen}.";
                }
            }

            // So khớp với RAM đã chọn
            if ($ram) {
                $ramSpecs = json_decode($ram['specs'] ?? '', true) ?: [];
                $ramType = $ramSpecs['memory_type'] ?? '';

                if ($mbRamType !== '' && $ramType !== '' && strcasecmp($mbRamType, $ramType) !== 0) {
                    $blockers[] = "Bo mạch chủ chỉ hỗ trợ RAM chuẩn {$mbRamType}, không tương thích với RAM chuẩn {$ramType} đang chọn.";
                }
            }

            // So khớp với Case đã chọn
            if ($case) {
                $caseSpecs = json_decode($case['specs'] ?? '', true) ?: [];
                $caseFormFactors = $caseSpecs['supported_motherboard_form_factors'] ?? [];
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
            $ramModules = (int)($ramSpecs['modules'] ?? 1);

            // So khớp với Mainboard đã chọn
            if ($mainboard) {
                $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
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
        if ($candidateType === 'gpu') {
            $gpuSpecs = $candidateSpecs;
            $gpuLength = (float)($gpuSpecs['length_mm'] ?? 0);

            // So khớp với Case đã chọn
            if ($case) {
                $caseSpecs = json_decode($case['specs'] ?? '', true) ?: [];
                $caseMaxGpu = (float)($caseSpecs['max_gpu_length_mm'] ?? 300);

                if ($gpuLength > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Card màn hình dài {$gpuLength}mm, vượt quá giới hạn chiều dài VGA tối đa của thùng máy ({$caseMaxGpu}mm).";
                }
            }
        }

        // 5. Kiểm tra khi ứng cử viên là Nguồn máy tính (PSU)
        if ($candidateType === 'psu') {
            $psuSpecs = $candidateSpecs;
            $psuWattage = (float)($psuSpecs['wattage_w'] ?? 0);

            // Tính toán công suất đề xuất của cấu hình hiện tại
            $powerReq = self::calculatePowerRequirements($build);
            $recommendedWattage = $powerReq['recommended_psu_w'];

            if ($psuWattage > 0 && $psuWattage < $recommendedWattage) {
                $blockers[] = "Công suất nguồn ({$psuWattage}W) thấp hơn mức nguồn tối thiểu khuyến nghị cho cấu hình này ({$recommendedWattage}W).";
            }
        }

        // 6. Kiểm tra khi ứng cử viên là Thùng máy (Case)
        if ($candidateType === 'case') {
            $caseSpecs = $candidateSpecs;
            $caseFormFactors = $caseSpecs['supported_motherboard_form_factors'] ?? [];
            $caseMaxGpu = (float)($caseSpecs['max_gpu_length_mm'] ?? 0);
            $caseMaxCooler = (float)($caseSpecs['max_cpu_cooler_height_mm'] ?? 0);

            // So khớp với Mainboard đã chọn
            if ($mainboard) {
                $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
                $mbForm = $mbSpecs['form_factor'] ?? '';

                if ($mbForm !== '' && !empty($caseFormFactors) && !in_array($mbForm, $caseFormFactors)) {
                    $blockers[] = "Thùng máy không hỗ trợ kích thước Bo mạch chủ đang chọn ({$mbForm}).";
                }
            }

            // So khớp với GPU đã chọn
            if ($gpu) {
                $gpuSpecs = json_decode($gpu['specs'] ?? '', true) ?: [];
                $gpuLength = (float)($gpuSpecs['length_mm'] ?? 0);

                if ($gpuLength > 0 && $caseMaxGpu > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Thùng máy giới hạn VGA tối đa {$caseMaxGpu}mm, không vừa với Card màn hình đang chọn ({$gpuLength}mm).";
                }
            }

            // So khớp với Cooler đã chọn
            if ($cooler) {
                $coolerSpecs = json_decode($cooler['specs'] ?? '', true) ?: [];
                $coolerHeight = (float)($coolerSpecs['height_mm'] ?? 0);
                $coolerType = $coolerSpecs['cooler_type'] ?? '';

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
            $coolerType = $coolerSpecs['cooler_type'] ?? '';

            // So khớp với CPU đã chọn (Socket)
            if ($cpu) {
                $cpuSpecs = json_decode($cpu['specs'] ?? '', true) ?: [];
                $cpuSocket = $cpuSpecs['socket'] ?? '';

                if ($cpuSocket !== '' && !empty($coolerSockets) && !in_array($cpuSocket, $coolerSockets)) {
                    $blockers[] = "Tản nhiệt không hỗ trợ chân socket ({$cpuSocket}) của CPU đang chọn.";
                }
            }

            // So khớp với Case đã chọn (Chiều cao tản khí)
            if ($case && $coolerType === 'air') {
                $caseSpecs = json_decode($case['specs'] ?? '', true) ?: [];
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
