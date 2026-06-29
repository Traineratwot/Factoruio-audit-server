export type Finding = {
    type: string;
    description: string;
    severity?: 'low' | 'medium' | 'high';

    /**
     * potential saving in bytes if this finding is fixed.
     */
    potentialSavings?: number;

    /** Paths related to this finding, if any. */
    paths?: string[];
};

type ScannerReport = {
    id: string;
    score: number;
    weight: number;
    savings: number;
    findings: Finding[];
};

type AuditReport = {
    modName: string;
    modNameReadable: string;
    version: string;
    sha1: string;
    timestamp: number;
    scannerVersion: number;
    modSize?: number;
    score: number;
    potentialSavings?: number;
    percentageSavings?: number;
    scanners: ScannerReport[];
    preflightFindings?: Finding[];
    errors?: string[];
};
import z from 'zod';

const Release = z.object({
    download_url: z.string(),
    file_name: z.string(),
    info_json: z.object({
        factorio_version: z.string(),
    }),
    released_at: z.string(),
    sha1: z.string(),
    version: z.string(),
});
type Release = z.infer<typeof Release>;

const baseModInfo = z.object({
    category: z.string().nullable(),
    downloads_count: z.number(),
    name: z.string(),
    owner: z.string(),
    score: z.number().default(0),
    summary: z.string(),
    title: z.string(),
});

const ModInfo = baseModInfo.extend({
    releases: z.array(Release),
    thumbnail: z.string(),
});

type ModInfo = z.infer<typeof ModInfo>;

const ModListItem = baseModInfo.extend({
    latest_release: Release,
});
type ModListItem = z.infer<typeof ModListItem>;

export const ModList = z.object({
    results: z.array(ModListItem),
    pagination: z
        .object({
            count: z.number(),
            page: z.number(),
            page_count: z.number(),
            page_size: z.number(),
        })
        .nullable(),
});
type ModList = z.infer<typeof ModList>;

export type rawReport = {
    report: AuditReport;
    modInfo: ModListItem;
};

export interface Mod {
    id: number;
    name: string;
    owner: string;
    latest_version: string | null;
    category: string | null;
    title: string | null;
    summary: string | null;
    downloads_count: number | null;
    popularity: number | null;
    created_at: string;
    updated_at: string;
    report_url: string;
    score?: number | null;
    reports_count: number;
    image: string | null;
}

export interface PaginatedMods {
    data: Mod[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export type CategoryFilterState = Record<string, 'include' | 'exclude' | null>;

export type ReportFilterValue = 'all' | 'with' | 'without';

export interface ModVersion {
    id: number;
    version: string;
    factorio_version: string;
    released_at: string;
}

export interface ModSearchResult {
    id: number;
    name: string;
    title: string;
}
