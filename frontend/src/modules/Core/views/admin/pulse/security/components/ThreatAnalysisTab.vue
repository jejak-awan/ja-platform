<template>
  <div class="space-y-6">
    <div
      v-if="loading"
      class="flex items-center justify-center p-12"
    >
      <Loader2 class="w-8 h-8 text-primary" />
    </div>

    <template v-else-if="analysis">
      <!-- Summary Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              {{ $t('features.security.threatAnalysis.stats.uniqueIps') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ analysis.stats.unique_ips }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              {{ $t('features.security.threatAnalysis.stats.highRiskIps') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-destructive">
              {{ analysis.stats.high_risk_count }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              {{ $t('features.security.threatAnalysis.stats.detectedCampaigns') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-warning">
              {{ analysis.campaigns.length }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              {{ $t('features.security.threatAnalysis.stats.totalEvents') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ analysis.stats.total_events }}
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Campaigns -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>{{ $t('features.security.threatAnalysis.campaigns.title') }}</CardTitle>
              <Badge variant="outline">
                {{ $t('features.security.threatAnalysis.campaigns.detected', { count: analysis.campaigns.length }) }}
              </Badge>
            </div>
            <CardDescription>{{ $t('features.security.threatAnalysis.campaigns.description') }}</CardDescription>
          </CardHeader>
          <CardContent>
            <div
              v-if="analysis.campaigns.length === 0"
              class="flex flex-col items-center justify-center py-8 text-muted-foreground"
            >
              <ShieldCheck class="w-12 h-12 mb-2 opacity-20" />
              <p>{{ $t('features.security.threatAnalysis.campaigns.empty') }}</p>
            </div>
            <div
              v-else
              class="space-y-4"
            >
              <div
                v-for="(campaign, index) in analysis.campaigns"
                :key="index" 
                class="p-4 rounded-lg border bg-muted/30 hover:bg-muted/50"
              >
                <div class="flex items-start justify-between">
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-semibold text-sm capitalize">{{ campaign.type.replace('_', ' ') }}</span>
                      <Badge
                        variant="destructive"
                        class="text-[10px]"
                      >
                        {{ $t('features.security.threatAnalysis.campaigns.highRisk') }}
                      </Badge>
                    </div>
                    <p class="text-xs text-muted-foreground">
                      {{ campaign.description }}
                    </p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-bold">
                      {{ $t('features.security.threatAnalysis.campaigns.ipsCount', { count: campaign.ips.length }) }}
                    </div>
                    <div class="text-[10px] text-muted-foreground">
                      {{ $t('features.security.threatAnalysis.campaigns.coordinated') }}
                    </div>
                  </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-1">
                  <Badge
                    v-for="ip in campaign.ips.slice(0, 5)"
                    :key="ip"
                    variant="secondary"
                    class="text-[9px]"
                  >
                    {{ ip }}
                  </Badge>
                  <span
                    v-if="campaign.ips.length > 5"
                    class="text-[9px] text-muted-foreground ml-1"
                  >+{{ campaign.ips.length - 5 }} {{ $t('common.actions.more') }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- High Risk IPs -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>{{ $t('features.security.threatAnalysis.highRiskIps.title') }}</CardTitle>
              <Badge variant="outline">
                {{ $t('features.security.threatAnalysis.highRiskIps.flagged', { count: analysis.high_risk_ips.length }) }}
              </Badge>
            </div>
            <CardDescription>{{ $t('features.security.threatAnalysis.highRiskIps.description') }}</CardDescription>
          </CardHeader>
          <CardContent>
            <div
              v-if="analysis.high_risk_ips.length === 0"
              class="flex flex-col items-center justify-center py-8 text-muted-foreground"
            >
              <ShieldIcon class="w-12 h-12 mb-2 opacity-20" />
              <p>{{ $t('features.security.threatAnalysis.highRiskIps.empty') }}</p>
            </div>
            <div
              v-else
              class="max-h-[400px] overflow-y-auto pr-4"
            >
              <div class="space-y-3">
                <div
                  v-for="entry in analysis.high_risk_ips"
                  :key="entry.ip" 
                  class="flex items-center justify-between p-3 rounded-lg border bg-card"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
                      <Zap class="w-5 h-5 text-destructive" />
                    </div>
                    <div>
                      <div class="font-mono text-sm font-bold">
                        {{ entry.ip }}
                      </div>
                      <div class="text-[10px] text-muted-foreground">
                        {{ $t('features.security.threatAnalysis.highRiskIps.score') }}: {{ entry.score }} | {{ $t('features.security.threatAnalysis.highRiskIps.events') }}: {{ Object.values(entry.events as Record<string, number>).reduce((a: number, b: number) => a + b, 0) }}
                      </div>
                    </div>
                  </div>
                  <div class="flex flex-col items-end gap-1">
                    <Badge
                      :variant="entry.score > 50 ? 'destructive' : 'warning'"
                      class="text-[10px]"
                    >
                      {{ entry.score > 50 ? $t('features.security.threatAnalysis.highRiskIps.critical') : $t('features.security.threatAnalysis.highRiskIps.warning') }}
                    </Badge>
                    <Button
                      variant="ghost"
                      size="sm"
                      class="h-7 text-[10px]"
                      @click="$emit('block-ip', entry.ip)"
                    >
                      {{ $t('features.security.threatAnalysis.highRiskIps.actions.block') }}
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Auto-Tune Logs -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>{{ $t('features.security.threatAnalysis.autoTune.title') }}</CardTitle>
              <CardDescription>{{ $t('features.security.threatAnalysis.autoTune.description') }}</CardDescription>
            </div>
            <Button
              variant="outline"
              size="sm"
              @click="$emit('refresh')"
            >
              <RefreshCw class="w-4 h-4 mr-2" />
              {{ $t('common.actions.refresh') }}
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>{{ $t('features.security.threatAnalysis.autoTune.table.date') }}</TableHead>
                <TableHead>{{ $t('features.security.threatAnalysis.autoTune.table.changes') }}</TableHead>
                <TableHead>{{ $t('features.security.threatAnalysis.autoTune.table.stats') }}</TableHead>
                <TableHead>{{ $t('features.security.threatAnalysis.autoTune.table.campaigns') }}</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="log in autoTuneLogs"
                :key="log.id"
              >
                <TableCell class="font-medium whitespace-nowrap">
                  {{ new Date(log.created_at).toLocaleString() }}
                </TableCell>
                <TableCell>
                  <div class="flex flex-col gap-1">
                    <div
                      v-for="(change, i) in log.metadata?.changes || []"
                      :key="i"
                      class="text-xs flex items-center gap-1"
                    >
                      <div class="w-1 h-1 rounded-full bg-primary" />
                      {{ typeof change === 'string' ? change : $t(change.key, change.params || {}) }}
                    </div>
                    <span
                      v-if="!log.metadata?.changes?.length"
                      class="text-muted-foreground italic text-xs"
                    >{{ $t('features.security.threatAnalysis.autoTune.noChanges') }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="text-xs text-muted-foreground">
                    {{ $t('features.security.threatAnalysis.autoTune.ips') }}: {{ log.metadata?.stats?.unique_ips || 0 }} | 
                    {{ $t('features.security.threatAnalysis.autoTune.risk') }}: {{ log.metadata?.stats?.high_risk_count || 0 }}
                  </div>
                </TableCell>
                <TableCell>
                  <Badge
                    variant="secondary"
                    class="text-[10px]"
                  >
                    {{ $t('features.security.threatAnalysis.autoTune.detected', { count: log.metadata?.campaigns || 0 }) }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

<script setup lang="ts">
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import ShieldIcon from 'lucide-vue-next/dist/esm/icons/shield.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import { 
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Badge, Button,
    Table, TableHeader, TableRow, TableHead, TableBody, TableCell 
} from '@/components/ui';

interface HighRiskIp {
    ip: string;
    score: number;
    events: Record<string, number>;
}

interface Campaign {
    type: string;
    description: string;
    ips: string[];
}

interface ThreatAnalysisData {
    stats: {
        unique_ips: number;
        high_risk_count: number;
        total_events: number;
    };
    campaigns: Campaign[];
    high_risk_ips: HighRiskIp[];
}

interface AutoTuneChange {
    key: string;
    params?: Record<string, unknown>;
}

interface AutoTuneLog {
    id: number;
    created_at: string;
    metadata?: {
        changes?: (string | AutoTuneChange)[];
        stats?: { unique_ips?: number; high_risk_count?: number };
        campaigns?: number;
    };
}

defineProps<{
    analysis: ThreatAnalysisData | null;
    autoTuneLogs: AutoTuneLog[];
    loading: boolean;
}>();

defineEmits(['refresh', 'block-ip']);
</script>
