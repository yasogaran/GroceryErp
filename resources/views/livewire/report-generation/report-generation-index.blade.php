<div class="p-6">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Report Generation System</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Generate comprehensive reports with PDF and Excel export capabilities</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($reports as $report)
            <a href="{{ route($report['route']) }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-{{ $report['color'] }}-100 dark:bg-{{ $report['color'] }}-900 rounded-lg flex items-center justify-center mr-4">
                            @if($report['icon'] === 'cube')
                                <svg class="w-6 h-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            @elseif($report['icon'] === 'chart-bar')
                                <svg class="w-6 h-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            @elseif($report['icon'] === 'calculator')
                                <svg class="w-6 h-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @elseif($report['icon'] === 'document-text')
                                <svg class="w-6 h-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif($report['icon'] === 'book-open')
                                <svg class="w-6 h-6 text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $report['name'] }}</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $report['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-{{ $report['color'] }}-600 dark:text-{{ $report['color'] }}-400">
                            View Report →
                        </span>
                        <div class="flex gap-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Excel
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                PDF
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">About Report Generation</h4>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                    <li>• All reports support <strong>Excel (CSV)</strong> and <strong>PDF</strong> export formats</li>
                    <li>• Use filters to customize your report data before exporting</li>
                    <li>• PDF reports are optimized for browser print-to-PDF (use Ctrl+P in the PDF view)</li>
                    <li>• Excel exports are in CSV format, which opens perfectly in Microsoft Excel, Google Sheets, and LibreOffice</li>
                    <li>• All exports include timestamps in filenames for easy organization</li>
                </ul>
            </div>
        </div>
    </div>
</div>
