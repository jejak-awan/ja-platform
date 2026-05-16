<template>
  <TooltipProvider :delay-duration="400">
    <div
      v-if="editor"
      class="editor-toolbar bg-transparent flex flex-col"
    >
      <Tabs
        v-model="activeTab"
        class="w-full flex flex-col"
      >
        <!-- Tabs Header -->
        <div class="flex items-center justify-between px-3 py-1.5 bg-muted/5 h-10 shrink-0">
          <TabsList class="bg-muted/40 h-7 p-1 gap-1 rounded-md">
            <TabsTrigger
              value="home"
              class="h-5 px-3 text-[11px] font-medium rounded-sm transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              Home
            </TabsTrigger>
            <TabsTrigger
              value="insert"
              class="h-5 px-3 text-[11px] font-medium rounded-sm transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              Insert
            </TabsTrigger>
            <TabsTrigger
              value="layout"
              class="h-5 px-3 text-[11px] font-medium rounded-sm transition-colors data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
            >
              Layout
            </TabsTrigger>
            <TabsTrigger 
              v-if="editor.isActive('table')" 
              value="table" 
              class="h-5 px-3 text-[11px] font-medium rounded-sm transition-colors data-[state=active]:bg-background data-[state=active]:text-primary data-[state=active]:shadow-sm"
            >
              Table tools
            </TabsTrigger>
          </TabsList>

          <!-- Global Actions -->
          <div class="flex items-center gap-0.5 px-2">
            <TooltipRoot>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :disabled="!editor.can().undo()"
                  @click="editor.chain().focus().undo().run()"
                >
                  <Undo class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipPortal>
                <TooltipContent
                  class="tooltip-radix"
                  side="top"
                >
                  Undo (Ctrl+Z)
                </TooltipContent>
              </TooltipPortal>
            </TooltipRoot>

            <TooltipRoot>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :disabled="!editor.can().redo()"
                  @click="editor.chain().focus().redo().run()"
                >
                  <Redo class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipPortal>
                <TooltipContent
                  class="tooltip-radix"
                  side="top"
                >
                  Redo (Ctrl+Y)
                </TooltipContent>
              </TooltipPortal>
            </TooltipRoot>

            <div class="w-px h-4 bg-border/60 mx-1" />
                        
            <AiAssistPopover
              :context="getEditorContext"
              :disabled="!isAiEnabled"
              @result="handleAiResult"
            >
              <template #trigger>
                <Button
                  :disabled="!isAiEnabled"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50"
                >
                  <Sparkles class="w-3.5 h-3.5" />
                </Button>
              </template>
            </AiAssistPopover>

            <div class="w-px h-4 bg-border/60 mx-1" />
                        
            <TooltipRoot>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-sm"
                  :class="{ 'text-primary': showHtmlView }"
                  @click="$emit('toggleHtml')"
                >
                  <FileCode class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipPortal>
                <TooltipContent
                  class="tooltip-radix"
                  side="top"
                >
                  View HTML source
                </TooltipContent>
              </TooltipPortal>
            </TooltipRoot>
          </div>
        </div>

        <!-- Tab Content (Ribbon Content) -->
        <!-- ... existing content ... -->
        <div class="bg-background flex-1 overflow-x-auto custom-scrollbar">
          <!-- ... tabs content ... -->
          <TabsContent
            value="home"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <!-- ... -->
            <div class="ribbon-group border-r border-border/40">
              <!-- ... Font group ... -->
              <div class="ribbon-group-content">
                <Select
                  :model-value="getHeaderLevel()"
                  @update:model-value="setHeaderLevel"
                >
                  <SelectTrigger class="w-[100px] h-7 text-[11px] bg-transparent border-none shadow-none hover:bg-muted/40">
                    <SelectValue placeholder="Paragraph" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="p">
                      Paragraph
                    </SelectItem>
                    <SelectItem value="1">
                      Heading 1
                    </SelectItem>
                    <SelectItem value="2">
                      Heading 2
                    </SelectItem>
                    <SelectItem value="3">
                      Heading 3
                    </SelectItem>
                  </SelectContent>
                </Select>
                                
                <Select
                  :model-value="getFontFamily()"
                  @update:model-value="setFontFamily"
                >
                  <SelectTrigger class="w-[80px] h-7 text-[11px] bg-transparent border-none shadow-none hover:bg-muted/40">
                    <SelectValue placeholder="Font" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Inter">
                      Inter
                    </SelectItem>
                    <SelectItem value="serif">
                      Serif
                    </SelectItem>
                    <SelectItem value="monospace">
                      Mono
                    </SelectItem>
                  </SelectContent>
                </Select>

                <!-- Text Color Picker (Pop-over) -->
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <div class="flex items-center -ml-1">
                      <ColorPicker
                        v-model="selectedColor"
                        title="Text Color"
                      >
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-7 w-7 relative"
                          :class="{ 'text-primary': selectedColor }"
                        >
                          <Palette class="w-4 h-4" />
                          <div 
                            class="absolute bottom-1.5 w-3 h-0.5 rounded-full" 
                            :style="{ backgroundColor: selectedColor || 'currentColor', opacity: selectedColor ? 1 : 0.3 }" 
                          />
                        </Button>
                      </ColorPicker>
                    </div>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent
                      class="tooltip-radix"
                      side="top"
                    >
                      Text color
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
              </div>
              <div class="ribbon-group-label">
                Font
              </div>
            </div>
                        
            <!-- ... Style group ... -->
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content grid grid-cols-3 gap-x-0.5 gap-y-0.5 items-center">
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :class="{ 'text-primary': editor.isActive('bold') }"
                      @click="editor.chain().focus().toggleBold().run()"
                    >
                      <Bold class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Bold
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>

                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :class="{ 'text-primary': editor.isActive('italic') }"
                      @click="editor.chain().focus().toggleItalic().run()"
                    >
                      <Italic class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Italic
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>

                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :class="{ 'text-primary': editor.isActive('underline') }"
                      @click="editor.chain().focus().toggleUnderline().run()"
                    >
                      <UnderlineIcon class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Underline
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>

                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :class="{ 'text-primary': editor.isActive('strike') }"
                      @click="editor.chain().focus().toggleStrike().run()"
                    >
                      <Strikethrough class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Strikethrough
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>

                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      :class="{ 'text-primary': editor.isActive('code') }"
                      @click="editor.chain().focus().toggleCode().run()"
                    >
                      <Code class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Code
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>

                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-7"
                      @click="editor.chain().focus().unsetAllMarks().run()"
                    >
                      <RemoveFormatting class="w-3.5 h-3.5" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Clear style
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
              </div>
              <div class="ribbon-group-label">
                Style
              </div>
            </div>

            <!-- ... Paragraph group ... -->
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content grid grid-cols-4 gap-x-0.5 gap-y-0.5 items-center">
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'left' }) }"
                      @click="editor.chain().focus().setTextAlign('left').run()"
                    >
                      <AlignLeft class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Left
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'center' }) }"
                      @click="editor.chain().focus().setTextAlign('center').run()"
                    >
                      <AlignCenter class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Center
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'right' }) }"
                      @click="editor.chain().focus().setTextAlign('right').run()"
                    >
                      <AlignRight class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Right
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive({ textAlign: 'justify' }) }"
                      @click="editor.chain().focus().setTextAlign('justify').run()"
                    >
                      <AlignJustify class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Justify
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                                
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive('bulletList') }"
                      @click="editor.chain().focus().toggleBulletList().run()"
                    >
                      <List class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Bullets
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-6 w-6"
                      :class="{ 'text-primary': editor.isActive('orderedList') }"
                      @click="editor.chain().focus().toggleOrderedList().run()"
                    >
                      <ListOrdered class="w-3 h-3" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Numbers
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
              </div>
              <div class="ribbon-group-label">
                Paragraph
              </div>
            </div>

            <!-- ... Special group ... -->
            <div class="ribbon-group">
              <div class="ribbon-group-content flex gap-1">
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8"
                      :class="{ 'text-primary': editor.isActive('blockquote') }"
                      @click="editor.chain().focus().toggleBlockquote().run()"
                    >
                      <Quote class="w-4 h-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Blockquote
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
                <TooltipRoot>
                  <TooltipTrigger as-child>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="h-8 w-8"
                      :class="{ 'text-primary': editor.isActive('paragraph', { dropcap: true }) }"
                      @click="editor.chain().focus().toggleDropcap().run()"
                    >
                      <DropcapIcon class="w-4 h-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipPortal>
                    <TooltipContent class="tooltip-radix">
                      Dropcap
                    </TooltipContent>
                  </TooltipPortal>
                </TooltipRoot>
              </div>
              <div class="ribbon-group-label">
                Special
              </div>
            </div>
          </TabsContent>
          <TabsContent
            value="insert"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('openMedia')"
                >
                  <ImageIcon class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">Image</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('insertTable')"
                >
                  <TableIcon class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">Table</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                Common
              </div>
            </div>

            <div class="ribbon-group">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="$emit('insertHtml')"
                >
                  <FileCode2 class="w-5 h-5 opacity-70" />
                  <span class="text-[10px] font-medium">Embed</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                  @click="editor.chain().focus().setHorizontalRule().run()"
                >
                  <Minus class="w-5 h-5 opacity-50" />
                  <span class="text-[10px] font-medium">Line</span>
                </Button>
                <div class="flex flex-col items-center justify-center">
                  <IconPicker
                    model-value=""
                    @update:model-value="handleIconSelect"
                  >
                    <template #trigger>
                      <Button
                        variant="ghost"
                        size="sm"
                        class="h-11 px-4 flex flex-col gap-1 items-center justify-center hover:bg-muted/40 transition-colors"
                      >
                        <Smile class="w-5 h-5 opacity-70" />
                        <span class="text-[10px] font-medium">Icon</span>
                      </Button>
                    </template>
                  </IconPicker>
                </div>
              </div>
              <div class="ribbon-group-label">
                Embeds
              </div>
            </div>
          </TabsContent>
          <TabsContent
            value="layout"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  :class="{ 'text-primary': editor.isActive('textColumns', { count: 2 }) }"
                  @click="editor.chain().focus().toggleTextColumns(2).run()"
                >
                  <Columns class="w-4 h-4 opacity-70" />
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">2 columns</span>
                    <span class="text-[9px] opacity-60">Flow layout</span>
                  </div>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  :class="{ 'text-primary': editor.isActive('textColumns', { count: 3 }) }"
                  @click="editor.chain().focus().toggleTextColumns(3).run()"
                >
                  <div class="flex gap-0.5 opacity-50">
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                    <div class="w-0.5 h-3 bg-current rounded-full" />
                  </div>
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">3 columns</span>
                    <span class="text-[9px] opacity-60">Flow layout</span>
                  </div>
                </Button>
              </div>
              <div class="ribbon-group-label">
                Newspaper style
              </div>
            </div>
            <div class="ribbon-group">
              <div class="ribbon-group-content gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  @click="editor.chain().focus().insertColumns({ count: 2 }).run()"
                >
                  <LayoutGrid class="w-4 h-4 opacity-50" />
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">2 grid</span>
                    <span class="text-[9px] opacity-60">Containers</span>
                  </div>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 gap-2 font-normal"
                  @click="editor.chain().focus().insertColumns({ count: 3 }).run()"
                >
                  <div class="grid grid-cols-3 gap-0.5 opacity-50">
                    <div class="w-1 h-3 bg-current rounded-sm" />
                    <div class="w-1 h-3 bg-current rounded-sm" />
                    <div class="w-1 h-3 bg-current rounded-sm" />
                  </div>
                  <div class="flex flex-col items-start leading-none text-left">
                    <span class="text-[10px] font-medium">3 grid</span>
                    <span class="text-[9px] opacity-60">Containers</span>
                  </div>
                </Button>
              </div>
              <div class="ribbon-group-label">
                Grid system
              </div>
            </div>
          </TabsContent>
          <TabsContent
            v-if="editor.isActive('table')"
            value="table"
            class="m-0 p-1 flex items-stretch gap-1 h-full min-w-max pr-4 animate-in fade-in slide-in-from-top-1"
          >
            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center"
                  @click="editor.chain().focus().addColumnAfter().run()"
                >
                  <div class="relative">
                    <Table2 class="w-5 h-5 opacity-50" />
                    <Plus class="w-2 absolute -right-0.5 -bottom-0.5 bg-background rounded-full border border-primary text-primary" />
                  </div>
                  <span class="text-[10px] font-medium">Add col</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center"
                  @click="editor.chain().focus().addRowAfter().run()"
                >
                  <div class="relative">
                    <Table2 class="w-5 h-5 opacity-50 rotate-90" />
                    <Plus class="w-2 absolute -right-0.5 -bottom-0.5 bg-background rounded-full border border-primary text-primary" />
                  </div>
                  <span class="text-[10px] font-medium">Add row</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                Structure
              </div>
            </div>

            <div class="ribbon-group border-r border-border/40">
              <div class="ribbon-group-content">
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="editor.chain().focus().mergeCells().run()"
                >
                  <Merge class="w-3.5 h-3.5 opacity-70" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="editor.chain().focus().splitCell().run()"
                >
                  <Split class="w-3.5 h-3.5 opacity-70" />
                </Button>
              </div>
              <div class="ribbon-group-label">
                Merge
              </div>
            </div>

            <div class="ribbon-group">
              <div class="ribbon-group-content gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive"
                  @click="editor.chain().focus().deleteColumn().run()"
                >
                  <Minus class="w-4 h-4" />
                  <span class="text-[10px] font-medium">Del col</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive"
                  @click="editor.chain().focus().deleteRow().run()"
                >
                  <Minus class="w-4 h-4" />
                  <span class="text-[10px] font-medium">Del row</span>
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-11 px-3 flex flex-col gap-1 items-center justify-center text-destructive border-l ml-1 pl-3"
                  @click="editor.chain().focus().deleteTable().run()"
                >
                  <Trash2 class="w-4 h-4" />
                  <span class="text-[10px] font-medium">Delete</span>
                </Button>
              </div>
              <div class="ribbon-group-label">
                Danger
              </div>
            </div>
          </TabsContent>
        </div>
      </Tabs>
    </div>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { 
    Button, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem, 
    Tabs, 
    TabsList, 
    TabsTrigger, 
    TabsContent, 
    ColorPicker, 
    IconPicker 
} from '@/shared/components/ui';
import { 
    TooltipProvider, 
    TooltipRoot, 
    TooltipTrigger, 
    TooltipContent, 
    TooltipPortal 
} from 'radix-vue';

import Bold from 'lucide-vue-next/dist/esm/icons/bold.js';
import Italic from 'lucide-vue-next/dist/esm/icons/italic.js';
import UnderlineIcon from 'lucide-vue-next/dist/esm/icons/underline.js';
import Strikethrough from 'lucide-vue-next/dist/esm/icons/strikethrough.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import ListOrdered from 'lucide-vue-next/dist/esm/icons/list-ordered.js';
import Quote from 'lucide-vue-next/dist/esm/icons/quote.js';
import Minus from 'lucide-vue-next/dist/esm/icons/minus.js';
import Undo from 'lucide-vue-next/dist/esm/icons/undo.js';
import Redo from 'lucide-vue-next/dist/esm/icons/redo.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import RemoveFormatting from 'lucide-vue-next/dist/esm/icons/remove-formatting.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import TableIcon from 'lucide-vue-next/dist/esm/icons/table.js';
import Table2 from 'lucide-vue-next/dist/esm/icons/table.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Merge from 'lucide-vue-next/dist/esm/icons/merge.js';
import Split from 'lucide-vue-next/dist/esm/icons/split.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import FileCode2 from 'lucide-vue-next/dist/esm/icons/file-code.js';
import DropcapIcon from 'lucide-vue-next/dist/esm/icons/type.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Smile from 'lucide-vue-next/dist/esm/icons/smile.js';

import { useSystemStore } from '@/modules/System/stores/system';
import AiAssistPopover from '@/shared/components/editor/AiAssistPopover.vue';
import type { Editor } from '@tiptap/vue-3';

const props = defineProps<{
    editor: Editor | undefined;
    showHtmlView: boolean;
}>();

const emit = defineEmits<{
    (e: 'insertTable'): void;
    (e: 'openMedia'): void;
    (e: 'insertHtml'): void;
    (e: 'toggleHtml'): void;
    (e: 'insertIcon', iconName: string): void;
}>();

const coreStore = useSystemStore();
const activeTab = ref('home');

const handleIconSelect = (iconName: string) => {
    emit('insertIcon', iconName);
};

const handleAiResult = (text: string) => {
    if (!props.editor) return;
    
    const { from, to } = props.editor.state.selection;
    
    // If text was selected, replace it. Otherwise insert at cursor.
    if (from !== to) {
        props.editor.chain().focus().deleteSelection().insertContent(text).run();
    } else {
        props.editor.chain().focus().insertContent(text).run();
    }
};

const getEditorContext = computed(() => {
    if (!props.editor) return '';
    
    const { from, to } = props.editor.state.selection;
    
    // If text is selected, use that as context
    if (from !== to) {
        return props.editor.state.doc.textBetween(from, to, ' ');
    }
    
    // Otherwise use up to 5000 chars
    return props.editor.getText().slice(0, 5000);
});

// Sync selected color with editor state
const selectedColor = computed({
    get: () => props.editor?.getAttributes('textStyle').color || '',
    set: (color: string) => {
        if (!props.editor) return;
        if (color) {
            props.editor.chain().focus().setColor(color).run();
        } else {
            props.editor.chain().focus().unsetColor().run();
        }
    }
});

// Watch for table activity and auto-switch tab
watch(() => props.editor?.isActive('table'), (isInTable) => {
    if (isInTable) {
        activeTab.value = 'table';
    } else if (activeTab.value === 'table') {
        activeTab.value = 'home';
    }
}, { immediate: true });

const isAiEnabled = computed(() => {
    const enabled = coreStore.settings['ai_enabled'];
    const isEnabled = enabled && enabled !== '0' && enabled !== 'false';
    if (!isEnabled) return false;
    
    // Check if default provider has key
    const provider = (coreStore.settings['ai_default_provider'] as string) || 'gemini';
    return !!coreStore.settings[`${provider}_api_key`];
});

onMounted(() => {
    // Check if store has ai settings, if not fetch them
    if (!coreStore.settings['gemini_api_key']) {
        coreStore.fetchSettingsGroup('ai');
    }
});

function getHeaderLevel() {
    if (props.editor?.isActive('heading', { level: 1 })) return '1';
    if (props.editor?.isActive('heading', { level: 2 })) return '2';
    if (props.editor?.isActive('heading', { level: 3 })) return '3';
    return 'p';
}

function setHeaderLevel(val: string) {
    if (!props.editor) return;
    if (val === 'p') {
         props.editor.chain().focus().setParagraph().run();
    } else {
         props.editor.chain().focus().toggleHeading({ level: parseInt(val) as 1 | 2 | 3 | 4 | 5 | 6 }).run();
    }
}

function getFontFamily() {
    return props.editor?.getAttributes('textStyle').fontFamily || 'Inter';
}

function setFontFamily(val: string) {
    if (!props.editor) return;
    props.editor.chain().focus().setFontFamily(val).run();
}
</script>



<style scoped>
.editor-toolbar {
    transition: all 0.3s ease;
}

/* Ribbon Group Styling */
.ribbon-group {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-width: max-content;
    padding: 0.25rem 0.5rem 0.125rem;
}

.ribbon-group-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.ribbon-group-label {
    text-align: center;
    font-size: 10px;
    font-weight: 500;
    color: hsl(var(--muted-foreground) / 70%);
    margin-top: 0.125rem;
}

/* Radix Tooltip Styling */
.tooltip-radix {
    z-index: 1000;
    padding: 0.25rem 0.5rem;
    background-color: hsl(var(--popover));
    color: hsl(var(--popover-foreground));
    font-size: 10px;
    font-weight: 400;
    border-radius: 0.25rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: 1px solid hsl(var(--border));
    animation: fade-in 0.2s ease;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Scrollbar Styling for Ribbon Overflow */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--border) / 50%);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--border));
}

/* Refined Tab Styling */
[role="tab"] {
    position: relative;
    overflow: hidden;
}

/* keyboard support */
button:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px hsl(var(--background)), 0 0 0 4px hsl(var(--primary));
}
</style>
