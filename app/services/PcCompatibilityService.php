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

        // Nếu chưa chọn cả CPU và GPU thì chưa thể tính toán đáng tin cậy
        if (!$cpu && !$gpu) {
            return [
                'estimated_peak_w' => 0,
                'recommended_psu_w' => 0,
                'gpu_minimum_psu_w' => 0,
                'cpu_peak_w' => 0,
                'gpu_load_w' => 0,
                'details' => []
            ];
        }

        // 1. CPU Peak Power
        $cpuSpecs = $cpu ? (json_decode($cpu['specs'] ?? '', true) ?: []) : [];
        $cpuPeak = 0;
        if ($cpu) {
            if (isset($cpuSpecs['max_power_w'])) {
                $cpuPeak = (float)$cpuSpecs['max_power_w'];
            } elseif (isset($cpuSpecs['base_power_w'])) {
                $cpuPeak = (float)$cpuSpecs['base_power_w'];
            } else {
                $cpuPeak = 65; // fallback
            }
        }

        // 2. GPU Load Power
        $gpuSpecs = $gpu ? (json_decode($gpu['specs'] ?? '', true) ?: []) : [];
        $gpuLoad = $gpu ? (float)($gpuSpecs['board_power_w'] ?? 150) : 0;

        // 3. Motherboard Power (50W với phổ thông, 70W với high-end)
        $mbPower = 0;
        if ($mainboard) {
            $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
            $mbChipset = strtoupper($mbSpecs['chipset'] ?? '');
            $isHighEndMb = (strpos($mbChipset, 'Z') === 0 || strpos($mbChipset, 'X') === 0);
            $mbPower = $isHighEndMb ? 70 : 50;
        }

        // 4. RAM Power
        $ramPower = 0;
        if ($ram) {
            $ramSpecs = json_decode($ram['specs'] ?? '', true) ?: [];
            $ramPowerPerModule = (float)($ramSpecs['power_w_per_module'] ?? 4);
            $ramModulesCount = (int)($ramSpecs['modules'] ?? 2);
            $ramPower = $ramModulesCount * $ramPowerPerModule;
        }

        // 5. SSD & HDD Power
        $storagePower = 0;
        foreach ($storages as $st) {
            if ($st) {
                $stSpecs = json_decode($st['specs'] ?? '', true) ?: [];
                $storagePower += (float)($stSpecs['power_w'] ?? 6);
            }
        }

        // 6. Cooler Power (fan + pump)
        $coolerPower = 0;
        if ($cooler) {
            $coolerSpecs = json_decode($cooler['specs'] ?? '', true) ?: [];
            $coolerFanCount = (int)($coolerSpecs['fan_count'] ?? 1);
            $coolerFanPower = (float)($coolerSpecs['fan_power_w'] ?? 3);
            $coolerPumpPower = (float)($coolerSpecs['pump_power_w'] ?? 0);
            $coolerPower = $coolerPumpPower + ($coolerFanCount * $coolerFanPower);
        }

        // 7. Case Fans Power
        $fanPower = 0;
        foreach ($fans as $fn) {
            if ($fn) {
                $fnSpecs = json_decode($fn['specs'] ?? '', true) ?: [];
                $fanPower += (float)($fnSpecs['power_w'] ?? 3);
            }
        }
        if (empty($fans) && $case) {
            $fanPower = 9; // 3 fans default
        }

        // 8. USB / RGB dự phòng (chỉ cộng nếu có CPU hoặc mainboard)
        $usbMisc = ($cpu || $mainboard) ? 20 : 0;

        // Tổng công suất tải đỉnh ước tính (Estimated Peak Wattage)
        $estimatedPeak = $cpuPeak + $gpuLoad + $mbPower + $ramPower + $storagePower + $coolerPower + $fanPower + $usbMisc;

        // Tính công suất nguồn khuyến nghị với 30% Headroom, làm tròn lên bậc 50W
        $headroomPsu = ceil(($estimatedPeak * 1.30) / 50) * 50;

        // Mức đề xuất từ hãng GPU (Manufacturer Recommended PSU) làm mức sàn
        $gpuRecommended = $gpu ? (float)($gpuSpecs['minimum_system_psu_w'] ?? 550) : 0;

        // PSU Khuyến nghị
        $recommendedPsu = max($headroomPsu, $gpuRecommended);

        return [
            'estimated_peak_w' => $estimatedPeak,
            'recommended_psu_w' => $recommendedPsu,
            'gpu_minimum_psu_w' => $gpuRecommended,
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
            $cpuGen = $cpuSpecs['generation'] ?? '';

            if (isset($cpuSpecs['integrated_graphics']) && $cpuSpecs['integrated_graphics'] === false && !$gpu) {
                $warnings[] = "CPU này không tích hợp nhân đồ họa iGPU. Bạn nên trang bị thêm Card màn hình rời (VGA).";
            }

            // So khớp với Mainboard đã chọn
            if ($mainboard) {
                $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
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
                    $warnings[] = "CPU thế hệ {$cpuGen} cần cập nhật BIOS cho Bo mạch chủ {$mbSpecs['chipset']} trước khi lắp đặt.";
                }
            }
        }

        // 2. Kiểm tra khi ứng cử viên là Mainboard
        if ($candidateType === 'mainboard') {
            $mbSpecs = $candidateSpecs;
            $mbSocket = $mbSpecs['socket'] ?? '';
            $mbRamType = $mbSpecs['memory_type'] ?? '';
            $mbSupportedGens = $mbSpecs['bios_cpu_generations'] ?? [];
            $mbBiosGens = $mbSpecs['bios_warning_generations'] ?? [];

            // So khớp với CPU đã chọn
            if ($cpu) {
                $cpuSpecs = json_decode($cpu['specs'] ?? '', true) ?: [];
                $cpuSocket = $cpuSpecs['socket'] ?? '';
                $cpuGen = $cpuSpecs['generation'] ?? '';

                if ($mbSocket !== '' && $cpuSocket !== '' && strcasecmp($mbSocket, $cpuSocket) !== 0) {
                    $blockers[] = "Socket Bo mạch chủ ({$mbSocket}) không khớp với Socket của CPU ({$cpuSocket}).";
                }

                if ($cpuGen !== '' && !empty($mbSupportedGens) && !in_array($cpuGen, $mbSupportedGens)) {
                    $blockers[] = "Bo mạch chủ {$mbSpecs['chipset']} không hỗ trợ dòng CPU thế hệ {$cpuGen} đang chọn.";
                }

                if ($cpuGen !== '' && !empty($mbBiosGens) && in_array($cpuGen, $mbBiosGens)) {
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

                if ($gpuLength > 0 && $caseMaxGpu > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Card màn hình dài {$gpuLength}mm, vượt quá giới hạn chiều dài VGA tối đa của thùng máy ({$caseMaxGpu}mm).";
                }
            }
        }

        // 5. Kiểm tra khi ứng cử viên là Nguồn máy tính (PSU)
        if ($candidateType === 'psu') {
            $psuSpecs = $candidateSpecs;
            $psuWattage = (float)($psuSpecs['rated_power_w'] ?? 0);

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

            if ($mainboard) {
                $mbSpecs = json_decode($mainboard['specs'] ?? '', true) ?: [];
                $mbForm = $mbSpecs['form_factor'] ?? '';

                if ($mbForm !== '' && !empty($caseFormFactors) && !in_array($mbForm, $caseFormFactors)) {
                    $blockers[] = "Thùng máy không hỗ trợ kích thước Bo mạch chủ đang chọn ({$mbForm}).";
                }
            }

            if ($gpu) {
                $gpuSpecs = json_decode($gpu['specs'] ?? '', true) ?: [];
                $gpuLength = (float)($gpuSpecs['length_mm'] ?? 0);

                if ($gpuLength > 0 && $caseMaxGpu > 0 && $gpuLength > $caseMaxGpu) {
                    $blockers[] = "Thùng máy giới hạn VGA tối đa {$caseMaxGpu}mm, không vừa với Card màn hình đang chọn ({$gpuLength}mm).";
                }
            }

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

            if ($cpu) {
                $cpuSpecs = json_decode($cpu['specs'] ?? '', true) ?: [];
                $cpuSocket = $cpuSpecs['socket'] ?? '';

                if ($cpuSocket !== '' && !empty($coolerSockets) && !in_array($cpuSocket, $coolerSockets)) {
                    $blockers[] = "Tản nhiệt không hỗ trợ chân socket ({$cpuSocket}) của CPU đang chọn.";
                }
            }

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
